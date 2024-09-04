<?php

include'../connection.php';
session_start();

$stadium_id = $_GET['stadium_id'];
$is_deleted = '1';
$NULL = NULL;

//check if id exists
$selectSql = 'SELECT * FROM stadiums WHERE stadium_id = :stadium_id';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':stadium_id',$stadium_id);
$selectStatement->execute();

if($selectStatement->rowCount() == 0)
{
    $_SESSION['error'] = 'Stadion ID bestaat niet!';
    header('Location:../index.php?page=stadium_dashboard');
}
else
{
    //update teams that contain this stadium_id
    $updateTeamSql = 'UPDATE teams SET stadium_id = :NULL WHERE stadium_id = :stadium_id';
    $updateTeamStatement = $pdo->prepare($updateTeamSql);
    $updateTeamStatement->bindParam(':NULL',$NULL);
    $updateTeamStatement->bindParam(':stadium_id',$stadium_id);
    $updateTeamStatement->execute();

    //soft delete stadium
    $updateSql = 'UPDATE stadiums SET is_deleted = :is_deleted WHERE stadium_id = :stadium_id';
    $updateStatement = $pdo->prepare($updateSql);
    $updateStatement->bindParam(':is_deleted',$is_deleted);
    $updateStatement->bindParam(':stadium_id',$stadium_id);
    $updateStatement->execute();



    $_SESSION['success'] = 'Stadion succesvol verwijderd!';
    header('Location:../index.php?page=stadium_dashboard');
}
?>