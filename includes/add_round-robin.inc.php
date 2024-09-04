<?php

// Fetch teams
$teamsResult = $pdo->query("SELECT team_id, club FROM teams WHERE is_deleted = 0");
$teams = $teamsResult->fetchAll(PDO::FETCH_ASSOC);

// Fetch referees
$stmt = $pdo->query("SELECT user_id, firstname, infix, lastname FROM users WHERE role_id = 2 AND is_deleted = 0");
$referees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<body>
<h1>Add Round Robin Tournament</h1>
<br>
<form action="php/add_round-robin.php" method="post">
    <div id="select2-container">
        <p class="textcreateDE">Tournament naam Kiezen:</p>
        <input type="text" class="form-control" id="clubName" placeholder="Tournament name" name="name" required>
    </div>
    <div id="select2-container">
        <p class="textcreateDE">Datum Kiezen:</p>
        <input type="date" id="startdate" name="startdate" class="form-control" required>
    </div>
    <div id="select2-container">
        <p class="textcreateDE">team Kiezen:</p>
        <select class="form-select" name="teams[]" id="teams" multiple required>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['team_id']?>"><?= $team['club'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="select2-container">
        <p class="textcreateDE">Scheidsrechter Kiezen:</p>
        <select class="form-select" id="referee" name="refereeIds[]" data-placeholder="Scheidsrechters kiezen" multiple required>
            <?php foreach ($referees as $referee): ?>
                <option value="<?= $referee['user_id'] ?>"><?= htmlspecialchars($referee['firstname'] . ' ' . ($referee['infix'] ? $referee['infix'] . ' ' : '') . $referee['lastname']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <!-- Submit Button -->
    <input type="submit" value="Add Tournament" class="btn btn-primary" id="select2-container">
</form>

<script>
    $(document).ready(function() {
        $('#teams, #referee').select2({
            theme: "bootstrap-5",
            placeholder: "Select Teams",
            closeOnSelect: false,
        });
    });
</script>
</body>
