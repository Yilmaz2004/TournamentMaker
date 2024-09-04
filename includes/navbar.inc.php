<?php
include "connection.php";

// Define the current page, default to 'login'
$current_page = $_GET['page'] ?? 'login';

// Define the public pages that anyone can access
$publicPages = ['round-robin_view', 'tournament_dashboard', 'login', 'tournament_dashboard_guest'];

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        $navitems = array(
            array('tournament_dashboard', 'Toernooien'),
            array('teams_dashboard', 'Teams'),
            array('player_dashboard', 'Spelers'),
            array('stadium_dashboard', 'Stadions'),
            array('referee_dashboard', 'Scheidsrechters'),
            array('logout', 'Uitloggen'),
        );
    } elseif ($_SESSION['role'] == 'referee') {
        $navitems = array(
            array('tournament_dashboard', 'Toernooien'),
            array('logout', 'Uitloggen'),
        );
    } else {
        $navitems = array(
            array('logout', 'Uitloggen'),
        );
    }
} else {
    $navitems = array(
        array('login', 'Login'),
        array('tournament_dashboard_guest', 'Toernooi'),
    );
}

// Redirect to login if the user is trying to access a protected page without the necessary role
if (!in_array($current_page, $publicPages) && !isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}
?>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <?php foreach ($navitems as $navitem) { ?>
                <li class="nav-item <?php if ($navitem[0] === $current_page) echo 'active'; ?>">
                    <a class="nav-link" href="index.php?page=<?= $navitem[0] ?>"><?= $navitem[1] ?></a>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>
</body>
