<?php
require 'connection.php';

// Function to generate round-robin schedule
function generateRoundRobinSchedule($teams) {
    $schedule = [];
    if (count($teams) % 2 != 0) {
        $teams[] = null; // Add a dummy team for odd number of teams
    }
    $numTeams = count($teams);
    for ($round = 0; $round < $numTeams - 1; $round++) {
        $roundMatches = [];
        for ($i = 0; $i < $numTeams / 2; $i++) {
            $team1 = $teams[$i];
            $team2 = $teams[$numTeams - 1 - $i];
            if ($team1 !== null && $team2 !== null) {
                $roundMatches[] = [$team1, $team2];
            }
        }
        $schedule[] = $roundMatches;
        $teams = array_merge(
            [array_shift($teams)],
            array_merge([array_pop($teams)], $teams)
        );
    }
    return $schedule;
}

// Fetch teams from the database
$tournamentId = 1; // Replace with your actual tournament ID
$stmt = $pdo->prepare("SELECT t.team_id, t.club FROM teams t
    JOIN tournaments_teams tt ON t.team_id = tt.team_id
    WHERE tt.tournament_id = :tournament_id ");
$stmt->execute(['tournament_id' => $tournamentId]);
$teams = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

if (count($teams) < 2) {
    die("Not enough teams to create a tournament.");
}

$schedule = generateRoundRobinSchedule($teams);

// Save the schedule to the database
$stmt = $pdo->prepare("INSERT INTO tournamentmatch (tournament_id, team1_id, team2_id, matchround) VALUES (:tournament_id, :team1_id, :team2_id, :matchround)");
foreach ($schedule as $round => $matches) {
    foreach ($matches as $match) {
        $stmt->execute([
            'tournament_id' => $tournamentId,
            'team1_id' => $match[0],
            'team2_id' => $match[1],
            'matchround' => $round + 1
        ]);
    }
}

echo "Tournament schedule generated and saved successfully.";
?>
