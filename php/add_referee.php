<?php
session_start();
include "../connection.php";// Ensure you have a file to handle the database connection.

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $firstname = $_POST['firstname'];
    $infix = $_POST['infix'];
    $lastname = $_POST['lastname'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = '2';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header("Location: ../index.php?page=add_referee&error=invalid-email&clear_errors=1");
        exit;
    }

    // Validation and error handling logic here
    if ($password !== $confirm_password) {
        header("Location: ../index.php?page=add_referee&error=invalid-password&clear_errors=1");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?page=add_referee&error=invalid-email-write&clear_errors=1");
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO users (email, firstname, infix, lastname, password, role_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $firstname, $infix, $lastname, $hashed_password, $role]);

    if ($stmt->rowCount()) {
        header("Location: ../index.php?page=referee_dashboard");
    } else {
        echo "Er is een fout opgetreden.";
    }
}
?>
