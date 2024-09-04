<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'referee' && $_SESSION['role'] !== 'admin')) {
    header("Location: index.php?page=login");
    exit;
}
require 'connection.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

if ($userRole == 'referee') {
    // Fetch tournaments assigned to the logged-in referee
    $tournamentStmt = $pdo->prepare("
        SELECT t.tournament_id, t.name 
        FROM tournaments t
        JOIN tournaments_referees tr ON t.tournament_id = tr.tournament_id
        WHERE tr.user_id = :user_id AND t.tournamenttype_id = '2'
    ");
    $tournamentStmt->execute([':user_id' => $userId]);
} else {
    // Fetch all tournaments for admin
    $tournamentStmt = $pdo->query("SELECT tournament_id, name FROM tournaments t WHERE t.tournamenttype_id = '2'");
}

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
    <h1 class="tournament-selection-h1">Kies een Toernooi</h1>
    <form method="GET" action="index.php" class="tournament-selection-form">
        <input type="hidden" name="page" value="<?php if ($userRole == 'admin'){?>view_upperbracketDEtournament_admin<?php }else {?>view_upperbracketDEtournament<?php }?>">
        <div class="form-group">
            <input class="tour-input" type="text" id="search-tournament" placeholder="Search tournament...">
        </div>
        <div class="tournament-selection-container">
            <?php foreach ($tournaments as $tournament_id): ?>
                <div class="tournament-selection-box">
                    <input type="radio" name="tournament_id" value="<?= $tournament_id['tournament_id'] ?>" id="tournament_id-<?= $tournament_id['tournament_id'] ?>">
                    <label for="tournament_id-<?= $tournament_id['tournament_id'] ?>"><?= htmlspecialchars($tournament_id['name']) ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>
