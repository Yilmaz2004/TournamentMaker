<?php

include'../connection.php';
session_start();

$_SESSION['form_values'] = $_POST;

$stadium_id = $_POST['stadium_id'];
$name = $_POST['name'];
$seats = $_POST['seats'];
$address = $_POST['address'];

//get old image in case the admin doesn't want to change the image
$selectSql = 'SELECT image FROM stadiums WHERE stadium_id = :stadium_id';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':stadium_id',$stadium_id);
$selectStatement->execute();
$result = $selectStatement->fetch(PDO::FETCH_ASSOC);

//check if stadium name is taken
$checkNameSql = 'SELECT * FROM stadiums WHERE name = :name AND stadium_id != :stadium_id';
$checkStatement = $pdo->prepare($checkNameSql);
$checkStatement->bindParam(':name',$name);
$checkStatement->bindParam(':stadium_id',$stadium_id);
$checkStatement->execute();

//check if stadium address is taken
$checkAddressSql = 'SELECT * FROM stadiums WHERE address = :address AND stadium_id != :stadium_id';
$checkAddressStatement = $pdo->prepare($checkAddressSql);
$checkAddressStatement->bindParam(':address',$address);
$checkAddressStatement->bindParam(':stadium_id',$stadium_id);
$checkAddressStatement->execute();

if(ISSET($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK)
{
    $fileContent = file_get_contents($_FILES['image']['tmp_name']);
}
else
{
       $fileContent = $result['image'];
}


//check if seats is a negative number
if($_POST['seats'] <= 0)
{
    $_SESSION['error'] = 'Het aantal stoelen kunnen niet negatief zijn!';
    header('Location:../index.php?page=edit_stadium&stadium_id=' . $_POST['stadium_id'] . '');
}

//check if there are no existing rows with the same name
else if ($checkStatement->rowCount() > 0)
{
    $_SESSION['error'] = 'Deze naam is al in gebruik!';
    header('Location:../index.php?page=edit_stadium&stadium_id=' . $_POST['stadium_id'] . '');
}

//check if there are no existing rows with the same address
else if ($checkAddressStatement->rowCount() > 0)
{
    $_SESSION['error'] = 'Dit adres is al in gebruik!';
    header('Location:../index.php?page=edit_stadium&stadium_id=' . $_POST['stadium_id'] . '');
}

else
{
    //update stadium info
    $updateSql = 'UPDATE stadiums SET image = :image, name = :name, seats = :seats, address = :address WHERE stadium_id = :stadium_id';
    $updateStatement = $pdo->prepare($updateSql);
    $updateStatement->bindParam(':image',$fileContent, PDO::PARAM_LOB);
    $updateStatement->bindParam(':name',$name);
    $updateStatement->bindParam(':seats',$seats);
    $updateStatement->bindParam(':address',$address);
    $updateStatement->bindParam(':stadium_id',$stadium_id);
    $updateStatement->execute();

    $_SESSION['success'] = 'Stadion succesvol aangepast!';
    header ('Location:../index.php?page=stadium_dashboard');
}

?>