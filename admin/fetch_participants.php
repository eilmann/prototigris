<?php
include('../includes/db.php');

if (isset($_GET['tournamentID'])) {
    $tournamentID = $_GET['tournamentID'];

    $sql = "SELECT participants.participantID, participants.participantName 
            FROM participants 
            JOIN tournament_registrations ON participants.participantID = tournament_registrations.participantID 
            JOIN forms ON tournament_registrations.formID = forms.formID 
            WHERE forms.tournamentID = '$tournamentID'";
    $result = $conn->query($sql);

    $participants = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($participants);
}
?>
