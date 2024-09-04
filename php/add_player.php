<?php
session_start();
include'../connection.php';


$firstname = $_POST['firstname'];
$infix = $_POST['infix'];
$lastname = $_POST['lastname'];
$birthdate = $_POST['birthdate'];
$leg = $_POST['leg_id'];
$position = $_POST['position_id'];
$jerseynumber = $_POST['jerseynumber'];
$club = $_POST['club'];
$is_deleted = '0';

if (ISSET($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK)
{
    $fileContent = file_get_contents($_FILES['image']['tmp_name']);
}
else
{
    $fileContent = file_get_contents('../images/alternateprofileimage.jpg');
}

//check if jerseynumber is a negative number
if($_POST['jerseynumber'] <= 0)
{
    $_SESSION['error'] = 'Rugnummer mag geen negatief getal zijn!';
    header('Location:../index.php?page=add_player');
}

else
{
    //insert speler gegevens
    $insertSql = 'INSERT INTO players (firstname, infix, lastname, birthdate, image, leg_id, position_id, jerseynumber, team_id, is_deleted) VALUES (:firstname, :infix, :lastname, :birthdate, :image, :leg_id, :position_id, :jerseynumber, :team_id, :is_deleted)';
    $insertStatement = $pdo->prepare($insertSql);
    $insertStatement->bindParam(':image', $fileContent, PDO::PARAM_LOB);
    $insertStatement->bindParam(':firstname',$firstname);
    $insertStatement->bindParam(':infix',$infix);
    $insertStatement->bindParam(':lastname',$lastname);
    $insertStatement->bindParam(':birthdate',$birthdate);
    $insertStatement->bindParam(':leg_id',$leg);
    $insertStatement->bindParam(':position_id',$position);
    $insertStatement->bindParam(':jerseynumber',$jerseynumber);
    $insertStatement->bindParam(':team_id',$club);
    $insertStatement->bindParam(':is_deleted', $is_deleted);
    $insertStatement->execute();

    $_SESSION['success'] = 'Speler succevol toegevoegd!';
    header('Location:../index.php?page=player_dashboard');
}
?>