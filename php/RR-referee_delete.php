<?php
require '../connection.php';

$tournamentId = $_GET['tournament_id'];
$refereeId = $_GET['user_id'];

// Delete the referee from the tournament
$deleteStmt = $pdo->prepare("DELETE FROM tournaments_referees WHERE tournament_id = :tournament_id AND user_id = :referee_id");
$deleteStmt->execute([':tournament_id' => $tournamentId, ':referee_id' => $refereeId]);

header("Location: ../index.php?page=round-robin_matches&tournament=$tournamentId");
exit;

?>