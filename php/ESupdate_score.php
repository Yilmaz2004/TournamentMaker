<?php
require '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tournamentId = $_POST['tournament_id'];

    // Function to update subsequent matches
    function updateSubsequentMatches($pdo, $matchId, $newWinner, $tournamentId) {
        $currentMatchStmt = $pdo->prepare("SELECT matchround FROM `tournamentmatch` WHERE tournamentmatch_id = :match_id");
        $currentMatchStmt->execute([':match_id' => $matchId]);
        $currentMatch = $currentMatchStmt->fetch(PDO::FETCH_ASSOC);
        $currentMatchRound = $currentMatch['matchround'];

        // Fetch subsequent match details
        $subsequentMatchStmt = $pdo->prepare("
            SELECT tournamentmatch_id, w1, w2, team1_id, team2_id 
            FROM `tournamentmatch` 
            WHERE (w1 = :currentMatchId OR w2 = :currentMatchId) 
            AND tournament_id = :tournament_id
            AND matchround = :next_round
        ");
        $nextRound = $currentMatchRound / 2;
        $subsequentMatchStmt->execute([
            ':currentMatchId' => $matchId,
            ':tournament_id' => $tournamentId,
            ':next_round' => $nextRound
        ]);
        $subsequentMatch = $subsequentMatchStmt->fetch(PDO::FETCH_ASSOC);

        if ($subsequentMatch) {
            $fieldToUpdate = ($subsequentMatch['w1'] == $matchId) ? 'team1_id' : 'team2_id';

            // Only update if the current match has a winner
            if ($newWinner !== null) {
                $updateSubsequentMatchStmt = $pdo->prepare("UPDATE `tournamentmatch` SET $fieldToUpdate = :new_winner WHERE tournamentmatch_id = :subsequent_match_id");
                $updateSubsequentMatchStmt->execute([
                    ':new_winner' => $newWinner,
                    ':subsequent_match_id' => $subsequentMatch['tournamentmatch_id']
                ]);

            }
        }
    }

    foreach ($_POST['matches'] as $matchId => $scores) {
        $score1 = $scores['score1'];
        $score2 = $scores['score2'];

        if ($score1 !== '' && $score2 !== '') {
            if (!is_numeric($score1) || !is_numeric($score2) || $score1 < 0 || $score2 < 0) {
                echo "<script>alert('Error: Kies een juist getal!'); window.history.back();</script>";
                exit;
            }
        } else {
            continue;
        }

        $currentMatchStmt = $pdo->prepare("SELECT matchround, team1_id, team2_id, score1, score2 
                                           FROM `tournamentmatch` 
                                           WHERE tournamentmatch_id = :match_id");
        $currentMatchStmt->execute([':match_id' => $matchId]);
        $currentMatch = $currentMatchStmt->fetch(PDO::FETCH_ASSOC);
        $currentMatchRound = $currentMatch['matchround'];

        // Update the current match scores
        $updateStmt = $pdo->prepare("UPDATE `tournamentmatch` SET score1 = :score1, score2 = :score2 WHERE tournamentmatch_id = :match_id");
        $updateStmt->execute([
            ':score1' => ($score1 === '' ? NULL : $score1),
            ':score2' => ($score2 === '' ? NULL : $score2),
            ':match_id' => $matchId
        ]);

        // Determine the winner
        $newWinner = null;
        if ($score1 !== '' && $score2 !== '') {
            if ($score1 > $score2) {
                $newWinner = $currentMatch['team1_id'];
            } elseif ($score2 > $score1) {
                $newWinner = $currentMatch['team2_id'];
            }

            if ($newWinner !== null) {
                // Update subsequent matches with the new winner
                updateSubsequentMatches($pdo, $matchId, $newWinner, $tournamentId);
            }
        }
    }


    header("Location: ../index.php?page=ESmatches&tournament=$tournamentId");
    exit;

}
?>
