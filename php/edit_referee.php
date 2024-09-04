<?php
session_start();
include "../connection.php";

if (isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];
    $email = $_POST['email'];
    $firstname = $_POST['firstname'];
    $infix = $_POST['infix'];
    $lastname = $_POST['lastname'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if email already exists for another user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        header("Location: ../index.php?page=edit_referee&error=invalid-email&id=$user_id");
        exit;
    }

    // Validation and error handling logic here
    if ($password !== $confirm_password) {
        header("Location: ../index.php?page=edit_referee&error=invalid-password&id=$user_id");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?page=edit_referee&error=invalid-email-write&id=$user_id");
        exit;
    }

    // Prepare the SQL statement without the password
    $sql = "UPDATE users SET email = ?, firstname = ?, infix = ?, lastname = ? WHERE user_id = ?";
    $params = [$email, $firstname, $infix, $lastname, $user_id];

    // Update the password if a new one is provided and valid
    if (!empty($password) && $password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET email = ?, firstname = ?, infix = ?, lastname = ?, password = ? WHERE user_id = ?";
        $params = [$email, $firstname, $infix, $lastname, $hashed_password, $user_id];
    } elseif (!empty($password)) {
        header("Location: ../index.php?page=edit_referee&error=password-mismatch&user_id=$user_id");
        exit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount()) {
        header("Location: ../index.php?page=referee_dashboard");
    } else {
        echo "No changes were made.";
    }
}
?>
