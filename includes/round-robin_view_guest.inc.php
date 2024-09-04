<?php
require 'connection.php';

// Fetch all tournaments as there's no user_id restriction
$tournamentStmt = $pdo->query("SELECT tournament_id, name FROM tournaments t WHERE t.tournamenttype_id = '3'");
$tournaments = $tournamentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<script>
    function filterTournaments() {
        const searchValue = document.getElementById('search-tournament').value.toLowerCase();
        const tournamentBoxes = document.querySelectorAll('.tournament-selection-box');
        tournamentBoxes.forEach(box => {
            const tournamentName = box.textContent.toLowerCase();
            if (tournamentName.includes(searchValue)) {
                box.style.display = '';
            } else {
                box.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tournamentBoxes = document.querySelectorAll('.tournament-selection-box');
        tournamentBoxes.forEach(box => {
            box.addEventListener('click', function () {
                const radio = this.querySelector('input[type="radio"]');
                tournamentBoxes.forEach(b => b.classList.remove('checked'));
                radio.checked = true;
                this.classList.add('checked');
                document.querySelector('.tournament-selection-form').submit();
            });
        });

        document.getElementById('search-tournament').addEventListener('input', filterTournaments);
    });
</script>
<div class="tournament-selection-body">
    <h1 class="tournament-selection-h1">Kiez een Toernooi</h1>
    <form method="GET" action="index.php" class="tournament-selection-form">
        <input type="hidden" name="page" value="round-robin_matches">
        <div class="form-group">
            <input class="tour-input" type="text" id="search-tournament" placeholder="Search tournament...">
        </div>
        <div class="tournament-selection-container">
            <?php foreach ($tournaments as $tournament): ?>
                <div class="tournament-selection-box">
                    <input type="radio" name="tournament" value="<?= $tournament['tournament_id'] ?>" id="tournament-<?= $tournament['tournament_id'] ?>">
                    <label for="tournament-<?= $tournament['tournament_id'] ?>"><?= ($tournament['name']) ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>
