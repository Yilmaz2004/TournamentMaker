<?php

include '../connection.php';
session_start();

$tournament_id = $_POST['tournament_id'];
$bracket = $_POST['bracket'];

// Get current referees bound to the tournament
$selectTournamentRefereesSql = 'SELECT user_id FROM tournaments_referees WHERE tournament_id = :tournament_id';
$selectTournamentRefereesStatement = $pdo->prepare($selectTournamentRefereesSql);
$selectTournamentRefereesStatement->bindParam(':tournament_id', $tournament_id);
$selectTournamentRefereesStatement->execute();
$currentReferees = $selectTournamentRefereesStatement->fetchAll(PDO::FETCH_COLUMN);

// Check which referees have been selected
$selectedReferees = isset($_POST['referees']) ? $_POST['referees'] : [];

// Insert new referees
foreach ($selectedReferees as $referee) {
    if (!in_array($referee, $currentReferees)) {
        $insertRefereeSql = 'INSERT INTO tournaments_referees (tournament_id, user_id) VALUES (:tournament_id, :user_id)';
        $insertRefereeStatement = $pdo->prepare($insertRefereeSql);
        $insertRefereeStatement->bindParam(':tournament_id', $tournament_id);
        $insertRefereeStatement->bindParam(':user_id', $referee);
        $insertRefereeStatement->execute();
    }
}

// Delete referees that are no longer selected
foreach ($currentReferees as $referee) {
    if (!in_array($referee, $selectedReferees)) {
        $deleteRefereeSql = 'DELETE FROM tournaments_referees WHERE tournament_id = :tournament_id AND user_id = :user_id';
        $deleteRefereeStatement = $pdo->prepare($deleteRefereeSql);
        $deleteRefereeStatement->bindParam(':tournament_id', $tournament_id);
        $deleteRefereeStatement->bindParam(':user_id', $referee);
        $deleteRefereeStatement->execute();
    }
}

$_SESSION['success'] = 'Scheidsrechters succesvol aangepast!';
if ($bracket == 'U') {
    header('Location:../index.php?page=view_upperbracketDEtournament_admin&tournament_id=' . $_POST['tournament_id'] . ' ');
    exit();
} else {
    header('Location:../index.php?page=view_lowerbracketDEtournament_admin&tournament_id=' . $_POST['tournament_id'] . '');
    exit();
}
?>