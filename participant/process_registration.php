<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate other form fields if needed

    // Get form data
    $formID = $_GET['formID'];
    $inputParticipantID = $_POST['participantID'];
    $sessionParticipantID = $_SESSION['participantID'];
    $submissionTime = date("Y-m-d H:i:s");

    // Check if the input Participant ID matches the one from the session
    if ($inputParticipantID !== $sessionParticipantID) {
        echo '<script>alert("Error: Participant ID does not match."); window.history.back();</script>';
        exit();
    }

    // Check if the participant exists in the participants table
    $checkParticipantSql = "SELECT participantID FROM participants WHERE participantID = ?";
    $checkParticipantStmt = $conn->prepare($checkParticipantSql);
    $checkParticipantStmt->bind_param("s", $sessionParticipantID);
    $checkParticipantStmt->execute();
    $checkParticipantStmt->store_result();

    if ($checkParticipantStmt->num_rows == 0) {
        // Participant does not exist
        echo '<script>alert("Error: Participant does not exist."); window.history.back();</script>';
        exit();
    }

    // Close the statement
    $checkParticipantStmt->close();

    // Check if an application already exists for the participant
    $checkExistingSql = "SELECT registrationID FROM tournament_registrations WHERE formID = ? AND participantID = ?";
    $checkStmt = $conn->prepare($checkExistingSql);
    $checkStmt->bind_param("ss", $formID, $sessionParticipantID);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Display a pop-up message
        echo '<script>alert("Error: Application already exists for this participant."); window.location.href = "../index.php";</script>';
        exit();
    }

    // Close the statement
    $checkStmt->close();

    // Check if proof of payment is enabled
    if (isset($_FILES['proofOfPayment'])) {
        $proofOfPaymentPath = uploadProofOfPayment($formID);
    } else {
        $proofOfPaymentPath = null;
    }

    // Insert data into tournament_registrations table using prepared statement
    $insertSql = "INSERT INTO tournament_registrations (formID, participantID, submission_time, proof_of_payment_path) 
                  VALUES (?, ?, ?, ?)";
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ssss", $formID, $sessionParticipantID, $submissionTime, $proofOfPaymentPath);

    if ($stmt->execute()) {
        // Display a pop-up message
        echo '<script>alert("Registration successful!"); window.location.href = "../index.php";</script>';
    } else {
        // Display a pop-up message with the error details
        echo '<script>alert("Error: ' . $insertSql . ' ' . $stmt->error . '");</script>';
    }

    // Close the statement
    $stmt->close();
} else {
    // If the form is not submitted, redirect to an error page or handle accordingly
    header("Location: error.php");
    exit();
}

// Function to upload proof of payment and return the file path
function uploadProofOfPayment($formID) {
    $targetDir = "../uploads/proof_of_payment/";
    $targetFile = $targetDir . basename($_FILES["proofOfPayment"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if the file is a pdf using MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileMimeType = finfo_file($finfo, $_FILES["proofOfPayment"]["tmp_name"]);
    finfo_close($finfo);

    if ($fileMimeType != "application/pdf") {
        echo "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check file size (10 MB)
    if ($_FILES["proofOfPayment"]["size"] > 10485760) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        exit();
    } else {
        // If everything is ok, try to upload file
        if (move_uploaded_file($_FILES["proofOfPayment"]["tmp_name"], $targetFile)) {
            return $targetFile; // Return the file path if the upload is successful
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }
    }
}

// Close the database connection
$conn->close();
?>
