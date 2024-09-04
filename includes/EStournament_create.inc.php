<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'referee' && $_SESSION['role'] !== 'admin')) {
    header("Location: index.php?page=login");
    exit;
}

// Fetch teams
$stmt = $pdo->query("SELECT team_id, logo, club FROM teams WHERE is_deleted = 0");
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch referees
$stmt = $pdo->query("SELECT user_id, firstname, infix, lastname FROM users WHERE role_id = 2 AND is_deleted = 0");
$referees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="headerDE">Single elimination toernooi aanmaken</h1>
<p class="textcreateDE">Toernooi naam</p>
<form method="post" action="php/EScreate_tournament.php" onsubmit="return validateForm();">
    <input class="textboxtournamentname" type="text" name="name" id="name" placeholder="Toernooi naam..." required><br>
    <p class="labelstartdateDE">Startdatum</p>
    <input class="textboxstartdateDE" type="date" name="startdate" id="startdate" required><br>
    <p class="teamstextcreateDE">Kies 2, 4, 8, 16 of 32 teams.</p>
    <div id="select2-container">
        <select class="form-select" name="teamIds[]" id="team-multiple-select-field" data-placeholder="Teams kiezen..." multiple required>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['club']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="select2-container">
    <p class="textcreateDE">Scheidsrechter Kiezen:</p>
        <select class="form-select" id="referee-multiple-select-field" name="refereeIds[]" data-placeholder="Scheidsrechter kiezen..." multiple required>
            <?php foreach ($referees as $referee): ?>
                <option value="<?= $referee['user_id'] ?>"><?= htmlspecialchars($referee['firstname'] . ' ' . ($referee['infix'] ? $referee['infix'] . ' ' : '') . $referee['lastname']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    <input class="buttonsubmitteams" type="submit" value="Maak toernooi">
</form>

<script>
    function isPowerOfTwo(n) {
        return (n & (n - 1)) === 0 && n !== 0;
    }

    function validateForm() {
        const selectedOptions = document.querySelectorAll('#team-multiple-select-field option:checked');
        if (selectedOptions.length < 2 || !isPowerOfTwo(selectedOptions.length)) {
            alert("Kies de juiste aantal teams!");
            return false;
        }
        return true;
    }

    $(document).ready(function() {
        $('#team-multiple-select-field').select2({
            theme: "bootstrap-5",
            placeholder: $('#team-multiple-select-field').data('placeholder'),
            closeOnSelect: false,
        });

        $('#referee-multiple-select-field').select2({
            theme: "bootstrap-5",
            placeholder: $('#referee-multiple-select-field').data('placeholder'),
            closeOnSelect: false,
        });
    });
</script>
