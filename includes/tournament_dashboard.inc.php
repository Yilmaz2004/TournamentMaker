<?php


$role = $_SESSION['role'] ?? NULL;
?>

<div class="tournament-buttons">
    <?php if ($role === 'admin'): ?>
        Kies een type toernooi
        <br>
        <button onclick="location.href='index.php?page=dashboard_DEtournament'">Double elimination tournament</button>
        <button onclick="location.href='index.php?page=EStournament_dashboard'">Single elimination tournament</button>
        <button onclick="location.href='index.php?page=round-robin_dashboard'">Round robin tournament</button>
    <?php elseif ($role === 'referee'): ?>
    Kies een type toernooi
    <br>
    <button onclick="location.href='index.php?page=dashboard_DEtournament'">Double elimination tournament</button>
    <button onclick="location.href='index.php?page=EStournament_view'">Single elimination tournament</button>
    <button onclick="location.href='index.php?page=round-robin_view'">Round robin tournament</button>
    <?php else: ?>
            Kies een type toernooi
            <br>
            <button onclick="location.href='index.php?page=dashboard_DEtournament'">Double elimination tournament</button>
            <button onclick="location.href='index.php?page=EStournament_dashboard'">Single elimination tournament</button>
            <button onclick="location.href='index.php?page=round-robin_view_guest'">Round robin tournament</button>

    <?php endif;?>
</div>
