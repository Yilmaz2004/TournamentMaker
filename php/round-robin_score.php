<?php
session_start();
require '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referee') {
    header("Location: ../index.php?page=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournamentId = $_POST['tournament_id'];
    $matches = $_POST['matches'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE tournamentmatch SET score1 = :score1, score2 = :score2 WHERE tournamentmatch_id = :match_id");

        foreach ($matches as $matchId => $scores) {
            $score1 = isset($scores['score1']) && $scores['score1'] !== '' ? $scores['score1'] : null;
            $score2 = isset($scores['score2']) && $scores['score2'] !== '' ? $scores['score2'] : null;

            // Controleer of de scores niet onder de 0 zijn
            if (($score1 !== null && $score1 < 0) || ($score2 !== null && $score2 < 0)) {
                throw new Exception('de score kan niet in de min.');
            }

            if ($score1 !== null || $score2 !== null) {
                $stmt->execute([
                    ':score1' => $score1,
                    ':score2' => $score2,
                    ':match_id' => $matchId
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = 'Scores updated successfully!';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error updating scores: ' . $e->getMessage();
    }

    header("Location: ../index.php?page=round-robin_matches&tournament=$tournamentId");
    exit;
}
?>
