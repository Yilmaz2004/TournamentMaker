<?php
require '../connection.php';

$tournamentId = $_GET['id'];

// Begin a transaction
$pdo->beginTransaction();

try {
    // Delete matches associated with the tournament
    $deleteMatchesStmt = $pdo->prepare("DELETE FROM `tournamentmatch` WHERE tournament_id = :tournament_id");
    $deleteMatchesStmt->execute([':tournament_id' => $tournamentId]);

    // Delete team associations with the tournament
    $deleteTeamsStmt = $pdo->prepare("DELETE FROM `tournaments_teams` WHERE tournament_id = :tournament_id");
    $deleteTeamsStmt->execute([':tournament_id' => $tournamentId]);

    // Delete referee associations with the tournament
    $deleteRefereesStmt = $pdo->prepare("DELETE FROM `tournaments_referees` WHERE tournament_id = :tournament_id");
    $deleteRefereesStmt->execute([':tournament_id' => $tournamentId]);

    // Delete the tournament itself
    $deleteTournamentStmt = $pdo->prepare("DELETE FROM `tournaments` WHERE tournament_id = :tournament_id");
    $deleteTournamentStmt->execute([':tournament_id' => $tournamentId]);

    // Commit the transaction
    $pdo->commit();

    // Redirect to the tournaments page with a success message
    header("Location: ../index.php?page=round-robin_view");
    exit;
} catch (Exception $e) {
    // Rollback the transaction if something failed
    $pdo->rollBack();
    echo 'fout';
    exit;
}
?>
