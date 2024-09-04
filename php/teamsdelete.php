<?php
include "../connection.php";

$team_id = $_GET['team_id'];

// Update the is_deleted column to 1 to "archive" the record
$stmt = $pdo->prepare("UPDATE teams SET is_deleted = 1 WHERE team_id = :team_id");
$stmt->bindParam(':team_id', $team_id);
$stmt->execute();

header('Location: ../index.php?page=teams_dashboard');
exit();
?>
