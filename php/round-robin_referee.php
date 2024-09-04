<?php
require '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tournamentId = $_POST['tournament_id'];
    $newRefereeIds = $_POST['refereeIds']; // Array of selected referee IDs

    // Check existing referees for the tournament
    $checkStmt = $pdo->prepare("SELECT user_id FROM tournaments_referees WHERE tournament_id = :tournament_id");
    $checkStmt->execute([':tournament_id' => $tournamentId]);
    $existingReferees = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

    // Insert new referees
    $insertStmt = $pdo->prepare("INSERT INTO tournaments_referees (tournament_id, user_id) VALUES (:tournament_id, :user_id)");
    foreach ($newRefereeIds as $refereeId) {
        if (!in_array($refereeId, $existingReferees)) {
            $insertStmt->execute([':tournament_id' => $tournamentId, ':user_id' => $refereeId]);
        }
    }

    header("Location: ../index.php?page=round-robin_matches&tournament=$tournamentId");
    exit;
}
?>