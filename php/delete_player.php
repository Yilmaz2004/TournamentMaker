<?php
include'../connection.php';
session_start();

$player_id = $_GET['player_id'];
$is_deleted = '1';
$team_id = NULL;

//check if id exists
$selectSql = 'SELECT player_id FROM players WHERE player_id = :player_id';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':player_id',$player_id);
$selectStatement->execute();

if($selectStatement->rowCount() > 0)
{
    //remove team_id from player
    $updateTeamSql = 'UPDATE players SET team_id = :team_id WHERE player_id = :player_id';
    $updateTeamStatement = $pdo->prepare($updateTeamSql);
    $updateTeamStatement->bindParam(':team_id',$team_id);
    $updateTeamStatement->bindParam(':player_id',$player_id);
    $updateTeamStatement->execute();

    //update is_deleted status for softdelete
    $updateSql = 'UPDATE players SET is_deleted = :is_deleted WHERE player_id = :player_id';
    $updateStatement = $pdo->prepare($updateSql);
    $updateStatement->bindParam(':is_deleted', $is_deleted);
    $updateStatement->bindParam(':player_id', $player_id);
    $updateStatement->execute();

    $_SESSION['success'] = 'Speler succesvol verwijdert!';
    header('Location:../index.php?page=player_dashboard');
}

else
{
    //if there is no record found
    $_SESSION['error'] = 'Speler niet gevonden!';
    header('Location: ../index.php?page=player_dashboard');
}
?>