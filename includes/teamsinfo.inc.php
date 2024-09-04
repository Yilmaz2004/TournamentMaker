<?php

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['id']) && !empty($_SESSION['id']);

$team_id = $_GET['team_id'];



if(isset($_GET['team_id']) && is_numeric($_GET['team_id'])) {
    // Filmsid uit de URL halen

    // SQL-query om filmgegevens op te halen
    $sql = "SELECT *
        FROM teams t
        JOIN stadiums s ON t.stadium_id = s.stadium_id
        WHERE t.team_id = :team_id"; // LEFT JOIN gebruikt om te voorkomen dat de query stopt als er geen classification is
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verdergaan met de rest van de code, ook als de classification niet is ingesteld
} else {
    // Redirecten naar de indexpagina als filmsid-parameter ontbreekt of ongeldig is
    header("Location: index.php");
    exit();
}

?>

<body>
<div class="container">
    <div class="film-image">
        <?php if(isset($row)) : ?>
            <img src="./media/<?php echo $row['logo']; ?>" width="200">
<!--            <img src="./media/--><?php //echo $row['image']; ?><!--" width="200">-->
        <?php else : ?>
            <p>No Image Available</p>
        <?php endif; ?>
    </div>
    <div class="film-detail">
        <?php if(isset($row)) : ?>
            <h2>team naam: <?= $row['club'] ?></h2>
            <p>stadium naam: <?= $row['name'] ?></p>
            <p>aantal zitplaatsen: <?= $row['seats'] ?> </p>
            <p>adres: <?= $row['address'] ?></p>
<!--            <p>Duration: --><?php //= $row['length'] ?><!-- minutes</p>-->
<!--            <p>genre: --><?php //= $row['genre'] ?><!-- </p>-->
<!--            <p>kijkwijzer:</p>-->
        <?php else : ?>
            <p>team not found.</p>
        <?php endif; ?>
    </div>
    <!-- Render buttons only if the user is logged in -->
    <?php if($isLoggedIn): ?>
        <div class="button-container">
            <button class="back-btn" onclick="window.location.href='index.php?page=teamsview'">
                films overzicht
            </button>
        </div>
    <?php endif; ?>
</div>
</body>

