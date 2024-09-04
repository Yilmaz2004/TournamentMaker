Hoi

<?php
include'../connection.php';
session_start();

$tournament_id = $_GET['tournament_id'];



//Delete tournament
$deleteSql = 'DELETE FROM tournaments WHERE tournament_id = :tournament_id';
$deleteStatement = $pdo->prepare($deleteSql);
$deleteStatement->bindParam(':tournament_id',$tournament_id);
$deleteStatement->execute();

$_SESSION['success'] = 'Toernooi succesvol verwijdert!';
header('Location:../index.php?page=tournament_dashboard');
?>