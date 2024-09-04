<?php
include'../connection.php';
session_start();


$name = $_POST['name'];
$seats = $_POST['seats'];
$address = $_POST['address'];

//check if stadium name is taken
$checkNameSql = 'SELECT * FROM stadiums WHERE name = :name';
$checkStatement = $pdo->prepare($checkNameSql);
$checkStatement->bindParam(':name',$name);
$checkStatement->execute();

//check if stadium address is taken
$checkAddressSql = 'SELECT * FROM stadiums WHERE address = :address';
$checkAddressStatement = $pdo->prepare($checkAddressSql);
$checkAddressStatement->bindParam(':address',$address);
$checkAddressStatement->execute();

if(ISSET($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK)
{
    $fileContent = file_get_contents($_FILES['image']['tmp_name']);
}
else
{
    $fileContent = file_get_contents('../images/alternatestadium.jpeg');
}

//check if seats are a negative number
if($_POST['seats'] <= 0)
{
    $_SESSION['error'] = 'Het aantal stoelen kan niet negatief zijn!';
    header('Location:../index.php?page=add_stadium');
}

//check if there are no existing rows with the same name
else if($checkStatement->rowCount() > 0)
{
    $_SESSION['error'] = 'Er bestaat al een stadion met deze naam!';
    header('Location:../index.php?page=add_stadium');
}

//check if there are no existing rows with the same address
else if($checkAddressStatement->rowCount() > 0)
{
    $_SESSION['error'] = 'Er bestaat al een stadion met dit adres!';
    header('Location:../index.php?page=add_stadium');
}

else
{
//insert stadium info
    $insertSql = 'INSERT INTO stadiums (image, name, seats, address) VALUES (:image, :name, :seats, :address)';
    $insertStatement = $pdo->prepare($insertSql);
    $insertStatement->bindParam(':image',$fileContent, PDO::PARAM_LOB);
    $insertStatement->bindParam(':name', $name);
    $insertStatement->bindParam(':seats',$seats);
    $insertStatement->bindParam(':address',$address);
    $insertStatement->execute();

    $_SESSION['success'] = 'Stadion succesvol toegevoegd!';
    header('Location:../index.php?page=stadium_dashboard');
}
?>