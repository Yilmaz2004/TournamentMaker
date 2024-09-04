<?php
session_start();
include '../connection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userId = $_GET['id'];

    // Prepare an update statement to mark the referee as deleted instead of actually deleting
    $stmt = $pdo->prepare("UPDATE users SET is_deleted = 1 WHERE user_id = :userId");

    // Bind the integer parameter for the user ID
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        // Redirect to a confirmation page or back to the main page with a success message
        header('Location: ../index.php?page=referee_dashboard');
    } else {
        // Redirect to an error page or back with an error message
        echo "Something went wrong";
    }

    // Close statement
    $stmt->close();
} else {
    // Redirect back if the ID is not valid
    header('Location: ../index.php?page=referee_dashboard');
}

// Close connection
$pdo = null;
?>
