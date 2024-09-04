<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'referee' && $_SESSION['role'] !== 'admin')) {
    header("Location: index.php?page=login");
    exit;
}

$role = $_SESSION['role'];
?>

<div class="tournament-buttons">
    <?php if ($role === 'admin'): ?>
        <button onclick="location.href='index.php?page=EStournament_create'">Toernooi Aanmaken</button>
    <?php endif; ?>
    <button onclick="location.href='index.php?page=EStournament_view'">Toernooi Kiezen</button>
</div>
