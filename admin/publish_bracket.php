<?php
include('../includes/db.php');
include('../includes/session.php');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Invalid data received']);
    exit;
}

$formID = $data['formID'];
$eliminationType = $data['eliminationType'];
$bracket = $data['bracket'];
$schedule = $data['schedule'];

// Generate bracket ID
$stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(bracketID, 3) AS UNSIGNED)) AS max_id FROM brackets");
$stmt->execute();
$stmt->bind_result($maxID);
$stmt->fetch();
$stmt->close();

$nextID = $maxID + 1;
$bracketID = "BR" . sprintf('%04d', $nextID);

// Save the bracket data
$bracketData = json_encode($bracket);
$sql = "INSERT INTO brackets (bracketID, formID, eliminationType, bracket) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $bracketID, $formID, $eliminationType, $bracketData);

if ($stmt->execute()) {
    // Save the schedule data
    foreach ($schedule as $entry) {
        // Generate schedule ID
        $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(scheduleID, 3) AS UNSIGNED)) AS max_id FROM schedules");
        $stmt->execute();
        $stmt->bind_result($maxID);
        $stmt->fetch();
        $stmt->close();

        $nextID = $maxID + 1;
        $scheduleID = "SC" . sprintf('%04d', $nextID);
        
        $matchLabel = $entry['match'];
        $matchDate = $entry['date'];
        $matchTime = $entry['time'];
        
        $sql = "INSERT INTO schedules (scheduleID, bracketID, match_label, match_date, match_time) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $scheduleID, $bracketID, $matchLabel, $matchDate, $matchTime);
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to save bracket data: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
