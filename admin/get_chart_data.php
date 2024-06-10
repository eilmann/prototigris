<?php
include ('../includes/db.php');

$data = [];

// Fetch total tournaments by month
$sql = "SELECT DATE_FORMAT(tournamentStartDate, '%m-%Y') AS month, COUNT(*) AS totalTournaments 
        FROM tournaments 
        GROUP BY month 
        ORDER BY month";
$result = $conn->query($sql);
$data['totalTournaments'] = [];
while ($row = $result->fetch_assoc()) {
    $data['totalTournaments'][] = $row;
}

// Fetch participants per tournament
$sql = "SELECT t.tournamentName, COUNT(tr.registrationID) AS participantCount
        FROM tournaments t
        LEFT JOIN tournament_registrations tr ON t.formID = tr.formID
        GROUP BY t.tournamentName";
$result = $conn->query($sql);
$data['participantsPerTournament'] = [];
while ($row = $result->fetch_assoc()) {
    $data['participantsPerTournament'][] = $row;
}

// Fetch match schedules
$sql = "SELECT s.match_date, COUNT(s.scheduleID) AS matchCount
        FROM schedules s
        GROUP BY s.match_date";
$result = $conn->query($sql);
$data['matchSchedules'] = [];
while ($row = $result->fetch_assoc()) {
    $data['matchSchedules'][] = $row;
}

// Fetch top participants
$sql = "SELECT p.participantName, pp.firstPlaceCount, pp.secondPlaceCount, pp.thirdPlaceCount
        FROM participants p
        INNER JOIN participant_performance pp ON p.participantID = pp.participantID
        ORDER BY pp.firstPlaceCount DESC, pp.secondPlaceCount DESC, pp.thirdPlaceCount DESC
        LIMIT 5";
$result = $conn->query($sql);
$data['topParticipants'] = [];
while ($row = $result->fetch_assoc()) {
    $data['topParticipants'][] = $row;
}

// Fetch tournaments by game title
$sql = "SELECT gameTitle, COUNT(tournamentID) AS tournamentCount
        FROM tournaments
        GROUP BY gameTitle";
$result = $conn->query($sql);
$data['tournamentsByGameTitle'] = [];
while ($row = $result->fetch_assoc()) {
    $data['tournamentsByGameTitle'][] = $row;
}

// Fetch most active participants
$sql = "SELECT p.participantName, COUNT(tr.registrationID) AS totalParticipant
        FROM participants p
        LEFT JOIN tournament_registrations tr ON p.participantID = tr.participantID
        GROUP BY p.participantName
        ORDER BY totalParticipant DESC
        LIMIT 5";
$result = $conn->query($sql);
$data['mostActiveParticipants'] = [];
while ($row = $result->fetch_assoc()) {
    $data['mostActiveParticipants'][] = $row;
}

// Top Participants by Game Title
$sql = "SELECT p.participantName, p.participantPic, t.gameTitle, COUNT(*) AS firstPlaceCount 
        FROM participants p
        JOIN tournaments t ON p.participantID = t.firstPlaceID
        GROUP BY p.participantID, t.gameTitle
        ORDER BY t.gameTitle, firstPlaceCount DESC";
$result = $conn->query($sql);
$data['topParticipantsByGame'] = [];
while ($row = $result->fetch_assoc()) {
    $data['topParticipantsByGame'][] = $row;
}

$conn->close();

echo json_encode($data);
?>
