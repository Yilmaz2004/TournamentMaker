<?php
session_start();
include "../connection.php"; // Make sure this path is correct
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['pass']);

    $sql = "SELECT users.user_id AS id, users.email AS email, users.password AS password, roles.role AS role FROM users JOIN roles ON users.role_id = roles.role_id WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($user = $stmt->fetch()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; // Store role in session

            switch ($user['role']) {
                case 'admin':
                    header("Location: ../index.php?page=referee_dashboard");
                    break;
                case 'referee':
                    header("Location: ../index.php?page=tournament_dashboard");
                    break;
                default:
                    header("Location: ../index.php?page=login&error=invalid-credentials");
                    break;
            }
            exit;
        } else {
            header("Location: ../index.php?page=login&error=invalid-credentials");
            exit;
        }
    } else {
        header("Location: ../index.php?page=login&error=invalid-credentials");
        exit;
    }
} else {
    // Redirect to the login page if the method is not POST
    header("Location: ../index.php?page=login");
    exit;
}
?>
