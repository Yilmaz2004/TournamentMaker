<?php
include'../connection.php';

$email = $_POST['email'];
$firstname = $_POST['voornaam'];
$lastname = $_POST['achternaam'];
$password = $_POST['wachtwoord'];
$role = '1';

$hashedpassword = password_hash($password, PASSWORD_DEFAULT);

$insertSql = 'INSERT INTO users VALUES ( NULL, :email, :firstname, NULL,:lastname, :password, :role_id)';
$insertSqlstatement = $pdo->prepare($insertSql);
$insertSqlstatement->bindParam(':email',$email);
$insertSqlstatement->bindParam(':firstname',$firstname);
$insertSqlstatement->bindParam(':lastname',$lastname);
$insertSqlstatement->bindParam(':password',$hashedpassword);
$insertSqlstatement->bindParam(':role_id',$role);
$insertSqlstatement->execute();

header('Location: index.php?page=login');
?>