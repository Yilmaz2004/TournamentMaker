<?php

require '../connection.php';

function isPowerOfTwo($n) {
    return ($n & ($n - 1)) === 0 && $n !== 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $startdate = $_POST['startdate'];
    $refereeIds = $_POST['refereeIds'];
    $typeId = 1; // Assuming typeId is always 1 for simplicity

    // Ensure teamIds is set and contains a number of teams that is a power of 2 and at least 2 teams
    if (!isset($_POST['teamIds']) || count($_POST['teamIds']) < 2 || !isPowerOfTwo(count($_POST['teamIds']))) {
        echo "<script>alert('Kies de juiste aantal teams!'); window.history.back();</script>";
        exit;
    }

    // Check if the tournament name already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournaments WHERE name = :name");
    $stmt->execute([':name' => $name]);
    $existingTournamentCount = $stmt->fetchColumn();

    if ($existingTournamentCount > 0) {
        echo "<script>alert('Deze naam bestaat al!'); window.history.back();</script>";
        exit;
    }

    // Insert the tournament
    $stmt = $pdo->prepare("INSERT INTO tournaments (name, startdate, tournamenttype_id) VALUES (:name, :startdate, :typeId)");
    $stmt->execute([':name' => $name, ':startdate' => $startdate, ':typeId' => $typeId]);
    $tournamentId = $pdo->lastInsertId();

    // Insert selected teams into tournaments_teams table
    $teamIds = $_POST['teamIds'];
    $stmt = $pdo->prepare("INSERT INTO tournaments_teams (tournament_id, team_id) VALUES (:tournament_id, :team_id)");

    foreach ($teamIds as $teamId) {
        $stmt->execute([':tournament_id' => $tournamentId, ':team_id' => $teamId]);
    }

    // Shuffle the team IDs for matchups
    shuffle($teamIds);

    $stmt = $pdo->prepare("INSERT INTO tournamentmatch (tournament_id, team1_id, team2_id, matchround, w1, w2) VALUES (:tournament_id, :team1_id, :team2_id, :matchround, NULL, NULL)");

    $teamsCount = count($teamIds);
    $currentRound = $teamsCount / 2;
    $matchIds = [];


    for ($i = 0; $i < $teamsCount; $i += 2) {
        $stmt->execute([
            ':tournament_id' => $tournamentId,
            ':team1_id' => $teamIds[$i],
            ':team2_id' => $teamIds[$i + 1],
            ':matchround' => $currentRound
        ]);
        $matchIds[] = $pdo->lastInsertId();
    }

    // Function to create next rounds
    function createNextRoundMatches($pdo, $tournamentId, $matchIds, $currentRound) {
        if (count($matchIds) <= 1) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO tournamentmatch (tournament_id, team1_id, team2_id, matchround, w1, w2) VALUES (:tournament_id, NULL, NULL, :matchround, :w1, :w2)");

        $nextRoundMatchIds = [];
        for ($i = 0; $i < count($matchIds); $i += 2) {
            $stmt->execute([
                ':tournament_id' => $tournamentId,
                ':matchround' => $currentRound / 2,
                ':w1' => $matchIds[$i],
                ':w2' => isset($matchIds[$i + 1]) ? $matchIds[$i + 1] : null
            ]);
            $nextRoundMatchIds[] = $pdo->lastInsertId();
        }

        createNextRoundMatches($pdo, $tournamentId, $nextRoundMatchIds, $currentRound / 2);
    }

    createNextRoundMatches($pdo, $tournamentId, $matchIds, $currentRound);

    // Add referees to the tournaments_referees table
    $stmt = $pdo->prepare("INSERT INTO tournaments_referees (tournament_id, user_id) VALUES (:tournament_id, :user_id)");
    foreach ($refereeIds as $refereeId) {
        $stmt->execute([':tournament_id' => $tournamentId, ':user_id' => $refereeId]);
    }

    header("Location: ../index.php?page=EStournament_view");
    exit();
}
?>
