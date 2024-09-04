<?php
session_start();
include'../connection.php';

$_SESSION['form_values'] = $_POST;

$player_id = $_POST['player_id'];
$firstname = $_POST['firstname'];
$infix = $_POST['infix'];
$lastname = $_POST['lastname'];
$birthdate = $_POST['birthdate'];
$leg = $_POST['leg_id'];
$position = $_POST['position_id'];
$jerseynumber = $_POST['jerseynumber'];
$club = $_POST['club'];

//Select old image in case the admin doesn't want to change the player image
$selectSql = 'SELECT image FROM players WHERE player_id = :player_id';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':player_id',$player_id);
$selectStatement->execute();
$result = $selectStatement->fetch(PDO::FETCH_ASSOC);

if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK)
{
    $fileContent = file_get_contents($_FILES['image']['tmp_name']);
}
else
{
    $fileContent = $result['image'];
}


//check if jerseynumber is a negative number
if($_POST['jerseynumber'] <= 0)
{
    $_SESSION['error'] = 'Rugnummer mag niet een negatief getal zijn!';
    header('Location:../index.php?page=edit_player&player_id=' . $_POST['player_id'] . '');
}
else
{
    //update player info
    $updateSql = 'UPDATE players SET firstname = :firstname, infix = :infix, lastname = :lastname, birthdate = :birthdate, image = :image, leg_id = :leg_id, position_id = :position_id, jerseynumber = :jerseynumber, team_id = :team_id WHERE player_id = :player_id';
    $updateStatement = $pdo->prepare($updateSql);
    $updateStatement->bindParam(':player_id',$player_id);
    $updateStatement->bindParam(':image', $fileContent, PDO::PARAM_LOB);
    $updateStatement->bindParam(':firstname',$firstname);
    $updateStatement->bindParam(':infix',$infix);
    $updateStatement->bindParam(':lastname',$lastname);
    $updateStatement->bindParam(':birthdate',$birthdate);
    $updateStatement->bindParam(':leg_id',$leg);
    $updateStatement->bindParam(':position_id',$position);
    $updateStatement->bindParam(':jerseynumber',$jerseynumber);
    $updateStatement->bindParam(':team_id',$club);
    $updateStatement->execute();

    $_SESSION['success'] = 'Speler succevol aangepast!';
    header('Location:../index.php?page=player_dashboard');
}
?>