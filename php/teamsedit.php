<?php
session_start();
include "../connection.php";

try {
    // Ensure all necessary POST keys are set
    if (!isset($_POST['team_id'], $_POST['club'], $_POST['stadium_id'])) {
        throw new Exception("Invalid input");
    }
    $team_id = $_POST['team_id'];
    $club = $_POST['club'];
    $stadium_id = $_POST['stadium_id'];

    // Check if a new logo has been uploaded
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name'] != '') {
        $logo = base64_encode(file_get_contents($_FILES['image']['tmp_name']));
    } else {
        // If no new logo, use the existing one
        $stmt = $pdo->prepare("SELECT logo FROM teams WHERE team_id = :team_id");
        $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $logo = $row['logo'];
        } else {
            throw new Exception("Team not found");
        }
    }

    // Check if the club name already exists with is_deleted = 0
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM teams WHERE club = :club AND is_deleted = 0 AND team_id != :team_id");
    $stmt->bindParam(':club', $club);
    $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        throw new Exception("Club name must be unique when is_deleted is 0");
    }

    // Update the team details
    $stmt = $pdo->prepare("UPDATE teams SET club = :club, stadium_id = :stadium_id, logo = :logo WHERE team_id = :team_id");
    $stmt->bindParam(':club', $club);
    $stmt->bindParam(':stadium_id', $stadium_id);
    $stmt->bindParam(':logo', $logo);
    $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['notification2'] = 'team updated successfully.';

    header('Location: ../index.php?page=teams_dashboard');
    exit();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
