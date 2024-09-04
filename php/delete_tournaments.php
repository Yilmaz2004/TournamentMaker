<?php
include'../connection.php';

$tournament_id = $_GET['tournament_id'];

$stmt1 = $pdo->prepare("DELETE FROM tournaments_teams WHERE tournament_id = :tournament_id");
$stmt1->bindParam(':tournament_id', $tournament_id);
$stmt1->execute();

$stmt = $pdo->prepare("DELETE FROM tournaments WHERE tournament_id = :tournament_id");
$stmt->bindParam(':tournament_id', $tournament_id);
$stmt->execute();


header('location: ../index.php?page=tournament_dashboard');
?>
