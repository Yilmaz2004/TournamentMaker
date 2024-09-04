<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'referee' && $_SESSION['role'] !== 'admin')) {
    header("Location: index.php?page=login");
    exit;
}
require 'connection.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

function generateRoundNames($numTeams) {
    $roundNames = [];
    $round = $numTeams;
    while ($round >= 1) {
        if ($round == 1) {
            $roundNames[$round] = 'Final';
        } elseif ($round == 2) {
            $roundNames[$round] = 'Semi Final';
        } elseif ($round == 4) {
            $roundNames[$round] = 'Quarter Final';
        } else {
            $roundNames[$round] = $round . ' Finals';
        }
        $round /= 2;
    }
    return $roundNames;
}

$tournamentId = $_GET['tournament'];

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
$allRefereeStmt = $pdo->query("SELECT user_id, firstname, infix, lastname FROM users WHERE role_id = '2'");
$referees = $allRefereeStmt->fetchAll(PDO::FETCH_ASSOC);

$finalWinner = null;
$finalWinnerName = null;

$numTeamsStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT team_id) as num_teams
    FROM (
        SELECT team1_id as team_id FROM `tournamentmatch` WHERE tournament_id = :tournament_id
        UNION ALL
        SELECT team2_id as team_id FROM `tournamentmatch` WHERE tournament_id = :tournament_id
    ) as team_ids
");
$numTeamsStmt->execute([':tournament_id' => $tournamentId]);
$numTeams = $numTeamsStmt->fetchColumn();

$roundNames = generateRoundNames($numTeams);

$matchStmt = $pdo->prepare("
    SELECT m.tournamentmatch_id, t1.club AS team1, t2.club AS team2, m.score1, m.score2, m.matchround, m.team1_id, m.team2_id 
    FROM `tournamentmatch` m
    JOIN teams t1 ON m.team1_id = t1.team_id
    JOIN teams t2 ON m.team2_id = t2.team_id
    WHERE m.tournament_id = :tournament_id
    ORDER BY m.matchround, m.tournamentmatch_id
");
$matchStmt->execute([':tournament_id' => $tournamentId]);
$matches = $matchStmt->fetchAll(PDO::FETCH_ASSOC);

$groupedMatches = [];
foreach ($matches as $match) {
    $groupedMatches[$match['matchround']][] = $match;
}

function determineNonEditableMatches($matches, $tournamentId, $pdo) {
    $nonEditableMatches = [];
    $matchWinners = [];

    // Determine the winner of each match
    foreach ($matches as $match) {
        if ($match['score1'] !== null && $match['score2'] !== null) {
            if ($match['score1'] > $match['score2']) {
                $matchWinners[$match['tournamentmatch_id']] = $match['team1_id'];
            } elseif ($match['score2'] > $match['score1']) {
                $matchWinners[$match['tournamentmatch_id']] = $match['team2_id'];
            }
        }
    }

    // Check each match to see if its winner has moved on to a subsequent round
    foreach ($matches as $match) {
        $winner = isset($matchWinners[$match['tournamentmatch_id']]) ? $matchWinners[$match['tournamentmatch_id']] : null;
        if ($winner) {
            $subsequentMatchStmt = $pdo->prepare("
                SELECT m.tournamentmatch_id
                FROM `tournamentmatch` m
                WHERE (m.team1_id = :winner OR m.team2_id = :winner)
                AND m.tournament_id = :tournament_id
                AND m.matchround < :currentRound
                AND (m.score1 IS NOT NULL OR m.score2 IS NOT NULL)
            ");
            $subsequentMatchStmt->execute([
                ':winner' => $winner,
                ':tournament_id' => $tournamentId,
                ':currentRound' => $match['matchround']
            ]);
            $subsequentMatch = $subsequentMatchStmt->fetch(PDO::FETCH_ASSOC);

            if ($subsequentMatch) {
                $nonEditableMatches[$match['tournamentmatch_id']] = true;
            }
        }
    }

    return $nonEditableMatches;
}

$nonEditableMatches = determineNonEditableMatches($matches, $tournamentId, $pdo);

foreach ($matches as $match) {
    if ($match['matchround'] == 1) {
        if ($match['score1'] !== null && $match['score2'] !== null) {
            if ($match['score1'] > $match['score2']) {
                $finalWinner = $match['team1_id'];
            }
            else if ($match['score1'] == $match['score2']){
                $finalWinner = null;
            }
            else {
                $finalWinner = $match['team2_id'];
            }

            // Fetch the team name for the final winner
            $teamNameStmt = $pdo->prepare("SELECT club FROM teams WHERE team_id = :team_id");
            $teamNameStmt->execute([':team_id' => $finalWinner]);
            $finalWinnerName = $teamNameStmt->fetchColumn();
        }
    }
}
?>

<div class="match-selection-body">
    <?php if ($userRole === 'admin'): ?>
        <div style="float: right;">
            <form method="POST" action="php/ESedit_referee.php">
                <input type="hidden" name="tournament_id" value="<?= $tournamentId ?>">
                <div id="select2-container-ST">
                    <p class="textcreateSE">Scheidsrechter Kiezen:</p>
                    <select class="form-select" id="referee-multiple-select-field" name="refereeIds[]" data-placeholder="Scheidsrechter kiezen..." multiple required>
                        <?php foreach ($referees as $referee): ?>
                            <option value="<?= $referee['user_id'] ?>"><?= htmlspecialchars($referee['firstname'] . ' ' . ($referee['infix'] ? $referee['infix'] . ' ' : '') . $referee['lastname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-sm tour-edit"><i class="bi bi-plus"></i></button>
            </form>
            <br>
            <p class="labelstartdateDE">Scheidsrechter:</p>
            <div class="referee-list">
                <?php foreach ($associatedReferees as $referee): ?>
                    <div class="referee-item">
                        <span class="referee-name"><?= htmlspecialchars($referee['firstname'] . ' ' . ($referee['infix'] ? $referee['infix'] . ' ' : '') . $referee['lastname']) ?></span><button class="btn btn-danger btn-sm"><a class="a-SE" href="php/ESdelete_referee.php?user_id=<?= $referee['user_id'] ?>&tournament_id=<?= $tournamentId ?>"><i class="bi bi-trash"></i></a></button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="php/ESdelete_tournament.php?id=<?= $tournamentId ?>" class="btn btn-danger btn-sm tour-delete" onclick="return confirm('Are you sure you want to delete this item?');">
            <i class="bi bi-trash tour-del-sym"></i>
        </a>
    <?php endif; ?>
    <h1 class="tournament-selection-h1"><?= htmlspecialchars($tournamentName) ?></h1>
    <?php if ($finalWinnerName): ?>
        <div class="final-winner">
            <h2>Winaar: <?= htmlspecialchars($finalWinnerName) ?></h2>
        </div>
    <?php endif; ?>
</div>
<?php if (!empty($groupedMatches)): ?>
    <form method="POST" action="php/ESupdate_score.php">
        <button class="update-single" type="submit"><i class="bi bi-check"></i></button>
        <input type="hidden" name="tournament_id" value="<?= $tournamentId ?>">
        <?php
        ksort($groupedMatches);
        foreach ($groupedMatches as $round => $matches): ?>
            <div class="round-container">
                <div class="round-label"><?= isset($roundNames[$round]) ? $roundNames[$round] : 'Round ' . $round ?></div>
                <div class="match-container">
                    <?php foreach ($matches as $match): ?>
                        <div class="match-box">
                            <span><?= htmlspecialchars($match['team1']) ?> vs <?= htmlspecialchars($match['team2']) ?></span>
                            <span>
                                <input type="text" name="matches[<?= $match['tournamentmatch_id'] ?>][score1]" value="<?= htmlspecialchars($match['score1']) ?>" <?= ($userRole === 'admin' || isset($nonEditableMatches[$match['tournamentmatch_id']])) ? 'readonly' : '' ?> class="<?= isset($nonEditableMatches[$match['tournamentmatch_id']]) ? 'non-editable' : '' ?>"> :
                                <input type="text" name="matches[<?= $match['tournamentmatch_id'] ?>][score2]" value="<?= htmlspecialchars($match['score2']) ?>" <?= ($userRole === 'admin' || isset($nonEditableMatches[$match['tournamentmatch_id']])) ? 'readonly' : '' ?> class="<?= isset($nonEditableMatches[$match['tournamentmatch_id']]) ? 'non-editable' : '' ?>">
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </form>
<?php else: ?>
    <p>No matches found for the selected tournament.</p>
<?php endif; ?>
<script>
    $(document).ready(function() {
        $('#referee-multiple-select-field').select2({
            theme: "bootstrap-5",
            placeholder: $('#referee-multiple-select-field').data('placeholder'),
            closeOnSelect: false,
        });
    });
</script>
