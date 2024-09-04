<?php
include '../connection.php';
session_start();

// Fetch teams
$teamsResult = $pdo->query("SELECT team_id, club FROM teams WHERE is_deleted = 0");
$teams = $teamsResult->fetchAll(PDO::FETCH_ASSOC);

// Fetch referees
$stmt = $pdo->query("SELECT user_id, firstname, infix, lastname FROM users WHERE role_id = 2 AND is_deleted = 0");
$referees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Server-side validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $startDate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
    $selectedTeams = isset($_POST['teams']) ? $_POST['teams'] : [];
    $selectedReferees = isset($_POST['refereeIds']) ? $_POST['refereeIds'] : [];

    if (empty($name) || empty($startDate) || empty($selectedTeams) || empty($selectedReferees)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: ../index.php?page=add_round-robin');
        exit();
    }

    $tournamentTypeId = '3';
    $teamCount = count($selectedTeams);

    // Check if the number of teams is between 2 and 20
    if ($teamCount < 2 || $teamCount > 20) {
        $_SESSION['error'] = 'Je moet tussen 2 en 20 teams selecteren!';
        header('Location: ../index.php?page=add_round-robin');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Insert the tournament into the database
        $stmt = $pdo->prepare("INSERT INTO tournaments (name, startdate, tournamenttype_id) VALUES (:name, :startdate, :tournamenttype_id)");
        $stmt->execute([
            ':name' => $name,
            ':startdate' => $startDate,
            ':tournamenttype_id' => $tournamentTypeId
        ]);

        // Get the last inserted tournament ID
        $tournamentId = $pdo->lastInsertId();

        // Insert the selected teams into the tournaments_teams table
        $stmt = $pdo->prepare("INSERT INTO tournaments_teams (tournament_id, team_id) VALUES (:tournament_id, :team_id)");
        foreach ($selectedTeams as $teamId) {
            $stmt->execute([
                ':tournament_id' => $tournamentId,
                ':team_id' => $teamId
            ]);
        }

        // Insert matches into the tournamentmatch table for a round-robin format
        $stmt = $pdo->prepare("INSERT INTO tournamentmatch (tournament_id, team1_id, team2_id) VALUES (:tournament_id, :team1_id, :team2_id)");

        for ($i = 0; $i < $teamCount; $i++) {
            for ($j = $i + 1; $j < $teamCount; $j++) {
                $stmt->execute([
                    ':tournament_id' => $tournamentId,
                    ':team1_id' => $selectedTeams[$i],
                    ':team2_id' => $selectedTeams[$j]
                ]);
            }
        }

        // Check and insert selected referees
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id AND role_id = 2 AND is_deleted = 0");
        foreach ($selectedReferees as $refereeId) {
            $stmt->execute([':user_id' => $refereeId]);
            $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userExists) {
                // Insert the referee into the tournaments_referees table
                $stmtInsertReferee = $pdo->prepare("INSERT INTO tournaments_referees (tournament_id, user_id) VALUES (:tournament_id, :user_id)");
                $stmtInsertReferee->execute([
                    ':tournament_id' => $tournamentId,
                    ':user_id' => $refereeId
                ]);
            } else {
                // Show error message if any referee does not exist
                $_SESSION['error'] = 'Een van de geselecteerde scheidsrechters bestaat niet.';
                header('Location: ../index.php?page=add_round-robin');
                exit();
            }
        }

        $pdo->commit();
        $_SESSION['success'] = 'Het toernooi is succesvol aangemaakt!';
        header('Location: ../index.php?page=round-robin_view');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Er is een fout opgetreden bij het aanmaken van het toernooi: ' . $e->getMessage();
        header('Location: ../index.php?page=add_round-robin');
        exit();
    }
}
?>
