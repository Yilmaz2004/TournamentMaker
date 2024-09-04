<?php
require 'connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'referee' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../index.php?page=login");
    exit;
}
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$tournamentId = $_GET['tournament'];

// Fetch tournament and referee details
$stmt = $pdo->prepare("
SELECT t.name AS tournament_name, u.firstname, u.infix, u.lastname
FROM tournaments t
LEFT JOIN tournaments_referees tr ON t.tournament_id = tr.tournament_id
LEFT JOIN users u ON tr.user_id = u.user_id
WHERE t.tournament_id = :tournament_id
");
$stmt->execute([':tournament_id' => $tournamentId]);
$tournamentDetails = $stmt->fetch(PDO::FETCH_ASSOC);
$tournamentName = $tournamentDetails['tournament_name'];
$refereeFullName = trim($tournamentDetails['firstname'] . ' ' . $tournamentDetails['infix'] . ' ' . $tournamentDetails['lastname']);

$refereeStmt = $pdo->query("SELECT u.user_id, u.firstname, u.infix, u.lastname 
                            FROM users u 
                            JOIN tournaments_referees tr ON u.user_id = tr.user_id 
                            WHERE role_id = '2' AND tr.tournament_id = '".$tournamentId."'");
$associatedReferees = $refereeStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all referees for the dropdown
$refereeStmt = $pdo->query("SELECT user_id, firstname, infix, lastname FROM users WHERE role_id = '2' and is_deleted = '0'");
$referees = $refereeStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the teams participating in the tournament
$teamsStmt = $pdo->prepare("
SELECT t.team_id, t.club
FROM tournaments_teams tt
JOIN teams t ON tt.team_id = t.team_id
WHERE tt.tournament_id = :tournament_id
ORDER BY t.team_id
");
$teamsStmt->execute([':tournament_id' => $tournamentId]);
$teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

// Generate Round Robin matches if not already generated
$checkMatchesStmt = $pdo->prepare("SELECT COUNT(*) FROM tournamentmatch WHERE tournament_id = :tournament_id");
$checkMatchesStmt->execute([':tournament_id' => $tournamentId]);
$matchCount = $checkMatchesStmt->fetchColumn();

//select tournamentname
$selectnameSql = 'SELECT tournaments.name, GROUP_CONCAT(CONCAT_WS(" ", users.firstname, users.infix, users.lastname) SEPARATOR "\n") as referees 
                  FROM tournaments 
                  LEFT JOIN tournaments_referees ON tournaments.tournament_id = tournaments_referees.tournament_id
                  LEFT JOIN users ON tournaments_referees.user_id = users.user_id
                  WHERE tournaments.tournament_id = :tournament_id';
$selectnameStatement = $pdo->prepare($selectnameSql);
$selectnameStatement->bindParam(':tournament_id',$tournamentId);
$selectnameStatement->execute();
$resultname = $selectnameStatement->fetch(PDO::FETCH_ASSOC);

if ($matchCount == 0) {
    function generateRoundRobinMatches($teams, $tournamentId, $pdo) {
        $numTeams = count($teams);
        $matches = [];
        // Generate round-robin matches
        for ($i = 0; $i < $numTeams - 1; $i++) {
            for ($j = $i + 1; $j < $numTeams; $j++) {
                $team1 = $teams[$i];
                $team2 = $teams[$j];

                $stmt = $pdo->prepare("INSERT INTO tournamentmatch (tournament_id, team1_id, team2_id) VALUES (:tournament_id, :team1_id, :team2_id)");
                $stmt->execute([
                    ':tournament_id' => $tournamentId,
                    ':team1_id' => $team1['team_id'],
                    ':team2_id' => $team2['team_id']
                ]);
                $matches[] = [
                    'team1' => $team1['club'],
                    'team2' => $team2['club']
                ];
            }
        }
        return $matches;
    }
    generateRoundRobinMatches($teams, $tournamentId, $pdo);
}
// Fetch matches for the selected tournament
$matchStmt = $pdo->prepare("
SELECT m.tournamentmatch_id, t1.club AS team1, t2.club AS team2, m.score1, m.score2
FROM tournamentmatch m
JOIN teams t1 ON m.team1_id = t1.team_id
JOIN teams t2 ON m.team2_id = t2.team_id
WHERE m.tournament_id = :tournament_id
ORDER BY t1.team_id, t2.team_id
");
$matchStmt->execute([':tournament_id' => $tournamentId]);
$matches = $matchStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<body>
<div class="container">
    <?php if ($userRole === 'admin'): ?>
        <div class="scorecard">
            <form method="POST" action="php/round-robin_referee.php">
                <input type="hidden" name="tournament_id" value="<?= htmlspecialchars($tournamentId) ?>">
                <div class="mb-3">
                    <label for="referee-multiple-select-field" class="form-label">Select Referees:</label>
                    <select class="form-select" id="referee-multiple-select-field" name="refereeIds[]" data-placeholder="Select Referees" multiple required>
                        <?php foreach ($referees as $referee): ?>
                            <option value="<?= $referee['user_id'] ?>"><?= htmlspecialchars($referee['firstname'] . ' ' . ($referee['infix'] ? $referee['infix'] . ' ' : '') . $referee['lastname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success"><i class="bi bi-plus"></i></button>
            </form>
            <hr>
            <p class="mt-3">Gekoppelde Scheidsrechters:</p>
            <div class="referee-list">
                <?php foreach ($associatedReferees as $referee): ?>
                    <div class="referee-item mb-2">
                        <span class="referee-name"><?= htmlspecialchars($referee['firstname'] . ' ' . ($referee['infix'] ? $referee['infix'] . ' ' : '') . $referee['lastname']) ?></span>
                        <a href="php/RR-referee_delete.php?user_id=<?= $referee['user_id'] ?>&tournament_id=<?= $tournamentId ?>" class="btn btn-danger btn-sm ms-2"><i class="bi bi-trash"></i> </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr>
            <div class="mt-3">
                <p>Toernooi verwijderen: <a href="php/round-robin_delete.php?id=<?= $tournamentId ?>" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je dit toernooi wilt verwijderen?');"><i class="bi bi-trash"></i></a></p>
            </div>
        </div>
    <?php endif; ?>


    <div class="matches-section">
        <?php if (!empty($matches)): ?>
            <form method="POST" action="php/round-robin_score.php">
                <h2>Wedstrijden</h2>
                <input type="hidden" name="tournament_id" value="<?= htmlspecialchars($tournamentId) ?>">
                <?php foreach ($matches as $match): ?>
                    <div class="match-box1">
                        <div class="team">
                            <?= htmlspecialchars($match['team1']) ?>
                        </div>
                        <?php if ($userRole === 'referee'): ?>
                            <div class="score-input">
                                <input type="number" name="matches[<?= $match['tournamentmatch_id'] ?>][score1]"
                                       value="<?= isset($match['score1']) ? htmlspecialchars($match['score1']) : '' ?>">
                            </div>
                        <?php else: ?>
                            <div class="score">
                                <span><?= isset($match['score1']) ? htmlspecialchars($match['score1']) : '' ?></span>
                            </div>
                        <?php endif; ?>
                        vs
                        <div class="score-divider"></div>
                        <?php if ($userRole === 'referee'): ?>
                            <div class="score-input">
                                <input type="number" name="matches[<?= $match['tournamentmatch_id'] ?>][score2]"
                                       value="<?= isset($match['score2']) ? htmlspecialchars($match['score2']) : '' ?>">
                            </div>
                        <?php else: ?>
                            <div class="score">
                                <span><?= isset($match['score2']) ? htmlspecialchars($match['score2']) : '' ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="team">
                            <?= htmlspecialchars($match['team2']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($userRole === 'referee'): ?>
                    <button type="submit" class="btn btn-primary mt-2">Scores bijwerken</button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <p>Er zijn nog geen wedstrijden.</p>
        <?php endif; ?>
    </div>





    <div class="scorecard-section">
        <?php
        // Function to calculate points
        function calculatePoints($score1, $score2) {
            if ($score1 > $score2) {
                return [3, 0]; // Team 1 wins
            } elseif ($score1 < $score2) {
                return [0, 3]; // Team 2 wins
            } else {
                return [1, 1]; // Draw
            }
        }

        // Fetch matches and initialize team statistics array
        $matchesStmt = $pdo->query("SELECT * FROM tournamentmatch WHERE tournament_id = $tournamentId");
        $matches = $matchesStmt->fetchAll(PDO::FETCH_ASSOC);

        $teamStats = [];
        foreach ($teams as $team) {
            $teamStats[$team['team_id']] = [
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'won' => 0,
                'lost' => 0,
                'drawn' => 0,
                'matches_played' => 0,
            ];
        }

        // Populate team statistics
        foreach ($matches as $match) {
            $team1_id = $match['team1_id'];
            $team2_id = $match['team2_id'];

            if (isset($teamStats[$team1_id]) && isset($teamStats[$team2_id]) && $match['score1'] !== null && $match['score2'] !== null) {
                $score1 = $match['score1'];
                $score2 = $match['score2'];
                list($points1, $points2) = calculatePoints($score1, $score2);

                $teamStats[$team1_id]['points'] += $points1;
                $teamStats[$team1_id]['goals_for'] += $score1;
                $teamStats[$team2_id]['points'] += $points2;
                $teamStats[$team2_id]['goals_for'] += $score2;
                $teamStats[$team1_id]['matches_played']++;
                $teamStats[$team2_id]['matches_played']++;

                if ($score1 !== null && $score2 !== null) {
                    $teamStats[$team2_id]['goals_against'] += $score1;
                    $teamStats[$team1_id]['goals_against'] += $score2;
                }

                if ($points1 == 3) {
                    $teamStats[$team1_id]['won']++;
                    $teamStats[$team2_id]['lost']++;
                } elseif ($points2 == 3) {
                    $teamStats[$team2_id]['won']++;
                    $teamStats[$team1_id]['lost']++;
                } else {
                    $teamStats[$team1_id]['drawn']++;
                    $teamStats[$team2_id]['drawn']++;
                }
            }
        }

        // Sort teams by points and goal difference
        uasort($teamStats, function ($a, $b) {
            if ($a['points'] == $b['points']) {
                return $b['goals_for'] - $a['goals_for']; // Sort by goals for if points are tied
            }
            return $b['points'] - $a['points']; // Otherwise, sort by points
        });
        ?>

        <div class="scorecard">
            <h1>toernooi naam: <?= htmlspecialchars($resultname['name']) ?></h1>
            <div class="container">
                <div class="left-column">
                    <h5>Scheidsrechters: <?= nl2br(htmlspecialchars($resultname['referees'])) ?>
                    </h5>
                </div>
                <div class="right-column">
                    <h1 class="text-center">Stand</h1>
                </div>
            </div>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Team</th>
                    <th>GS</th>
                    <th>W</th>
                    <th>V</th>
                    <th>G</th>
                    <th>DV</th>
                    <th>DT</th>
                    <th>DS</th>
                    <th>P</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($teamStats as $teamId => $stats): ?>
                    <?php
                    // Find team name by team ID
                    $teamName = '';
                    foreach ($teams as $team) {
                        if ($team['team_id'] == $teamId) {
                            $teamName = htmlspecialchars($team['club']);
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td><?= $teamName ?></td>
                        <td><?= $stats['matches_played'] ?></td>
                        <td><?= $stats['won'] ?></td>
                        <td><?= $stats['lost'] ?></td>
                        <td><?= $stats['drawn'] ?></td>
                        <td><?= $stats['goals_for'] ?></td>
                        <td><?= $stats['goals_against'] ?></td>
                        <td><?= $stats['goals_for'] - $stats['goals_against'] ?></td>
                        <td><?= $stats['points'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<script>
    $(document).ready(function() {
        $('#referee-multiple-select-field').select2({
            theme: "bootstrap-5",
            placeholder: "Select Referees",
            closeOnSelect: false,
        });
    });
</script>
</body>
</html>
