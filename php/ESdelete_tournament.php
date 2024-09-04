<?php
require '../connection.php';

    $tournamentId = $_GET['id'];

    // Begin a transaction
    $pdo->beginTransaction();

    try {
        // Delete the tournament
        $deleteTournamentStmt = $pdo->prepare("DELETE FROM `tournaments` WHERE tournament_id = :tournament_id");
        $deleteTournamentStmt->execute([':tournament_id' => $tournamentId]);

        // Commit the transaction
        $pdo->commit();

        // Redirect to the tournaments page with a success message
        header("Location: ../index.php?page=EStournament_view");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction if something failed
        $pdo->rollBack();
        exit;
    }
?>
