<?php
include('../includes/db.php');
include('../includes/session.php');

function generateSingleEliminationBracket($participants) {
    $rounds = [];
    $numParticipants = count($participants);

    // Handle byes for non-power of 2 participants
    $numRounds = ceil(log($numParticipants, 2));
    $totalSlots = pow(2, $numRounds);
    $numByes = $totalSlots - $numParticipants;

    for ($i = 0; $i < $numByes; $i++) {
        array_push($participants, null);
    }

    while (count($participants) > 1) {
        $round = [];
        for ($i = 0; $i < count($participants); $i += 2) {
            $round[] = [$participants[$i], $participants[$i + 1]];
        }
        $rounds[] = $round;
        $participants = array_map(function($match) { 
            return 'Winner of Match'; 
        }, $round);
        $participants = array_filter($participants);
    }

    return $rounds;
}

function generateDoubleEliminationBracket($participants) {
    $winnerBracket = generateSingleEliminationBracket($participants);
    $numRounds = count($winnerBracket);
    $loserBracket = array_fill(0, $numRounds, []);

    for ($round = 0; $round < $numRounds; $round++) {
        $numMatchesWB = count($winnerBracket[$round]);
        
        if ($round == 0) {
            // First round in loser's bracket gets half the matches of the first round in winner's bracket
            for ($i = 0; $i < $numMatchesWB / 2; $i++) {
                $loserBracket[$round][] = ['', ''];
            }
        } else if ($round == $numRounds - 1) {
            // Last round in loser's bracket has 1 match
            $loserBracket[$round][] = ['', ''];
        } else {
            // Middle rounds in loser's bracket match the count of the previous round in loser's bracket
            for ($i = 0; $i < count($loserBracket[$round - 1]); $i++) {
                $loserBracket[$round][] = ['', ''];
            }
        }
    }

    // Final match between the winners of the winner and loser brackets
    $finals = [['Winner of WB Final', 'Winner of LB Final']];

    return [
        'winnerBracket' => $winnerBracket,
        'loserBracket' => $loserBracket,
        'finals' => $finals
    ];
}

function generateMatchSchedule($numMatches, $startDate, $endDate, $startTime, $endTime, $matchDuration) {
    $schedule = [];
    $currentDate = new DateTime($startDate);
    $endDate = new DateTime($endDate);
    $currentDate->setTime(...explode(':', $startTime));
    $endDate->setTime(...explode(':', $endTime));
    $interval = new DateInterval('PT' . $matchDuration . 'M');
    $dayEndTime = clone $currentDate;
    $dayEndTime->setTime(...explode(':', $endTime));

    for ($i = 1; $i <= $numMatches; $i++) {
        if ($currentDate > $dayEndTime) {
            $currentDate->modify('+1 day');
            $currentDate->setTime(...explode(':', $startTime));
            $dayEndTime = clone $currentDate;
            $dayEndTime->setTime(...explode(':', $endTime));
        }

        if ($currentDate > $endDate) {
            break;
        }

        $schedule[] = [
            'match' => 'Match ' . chr(64 + $i),
            'date' => $currentDate->format('Y-m-d'),
            'time' => $currentDate->format('H:i')
        ];
        $currentDate->add($interval);
    }

    return $schedule;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formID = $_POST['formID'];
    $eliminationType = $_POST['eliminationType'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $matchDuration = $_POST['matchDuration'];

    $sql = "SELECT participants.participantName 
            FROM tournament_registrations 
            INNER JOIN participants ON tournament_registrations.participantID = participants.participantID 
            WHERE tournament_registrations.formID = ? AND tournament_registrations.status = 'approved'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        echo json_encode(['error' => 'Failed to prepare statement.']);
        exit();
    }
    $stmt->bind_param("s", $formID);
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        echo json_encode(['error' => 'Failed to execute statement.']);
        exit();
    }
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Failed to get result: " . $stmt->error);
        echo json_encode(['error' => 'Failed to get result.']);
        exit();
    }

    $participants = [];
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row['participantName'];
    }

    if (empty($participants)) {
        echo json_encode(['error' => 'No participants found for the selected tournament.']);
        exit();
    }

    shuffle($participants);

    if ($eliminationType == 'single') {
        $bracket = generateSingleEliminationBracket($participants);
    } else {
        $bracket = generateDoubleEliminationBracket($participants);
    }

    $numMatches = 0;
    if (isset($bracket['winnerBracket'])) {
        foreach ($bracket['winnerBracket'] as $round) {
            $numMatches += count($round);
        }
        foreach ($bracket['loserBracket'] as $round) {
            $numMatches += count($round);
        }
        $numMatches += 1; // Championship match
    } else {
        foreach ($bracket as $round) {
            $numMatches += count($round);
        }
    }

    $sql = "SELECT tournaments.tournamentStartDate, tournaments.tournamentEndDate FROM forms 
            INNER JOIN tournaments ON forms.tournamentID = tournaments.tournamentID
            WHERE forms.formID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        echo json_encode(['error' => 'Failed to prepare statement.']);
        exit();
    }
    $stmt->bind_param("s", $formID);
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        echo json_encode(['error' => 'Failed to execute statement.']);
        exit();
    }
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Failed to get result: " . $stmt->error);
        echo json_encode(['error' => 'Failed to get result.']);
        exit();
    }

    $tournament = $result->fetch_assoc();
    $startDate = $tournament['tournamentStartDate'];
    $endDate = $tournament['tournamentEndDate'];

    $schedule = generateMatchSchedule($numMatches, $startDate, $endDate, $startTime, $endTime, $matchDuration);

    header('Content-Type: application/json');
    echo json_encode(['bracket' => $bracket, 'schedule' => $schedule, 'formID' => $formID, 'eliminationType' => $eliminationType]);
    exit();
}

http_response_code(405);
header('Content-Type: application/json');
echo json_encode(["error" => "Method Not Allowed"]);

$conn->close();



