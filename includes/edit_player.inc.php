<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}

$player_id = $_GET['player_id'];

//ophalen van speler gegevens
$selectPlayerSql = 'SELECT players.player_id, players.image, players.firstname, players.infix, players.lastname, players.birthdate, players.leg_id, players.position_id, players.jerseynumber, teams.club, teams.team_id
              FROM players
              LEFT JOIN teams ON players.team_id = teams.team_id
              WHERE players.player_id = :player_id';
$selectStatement = $pdo->prepare($selectPlayerSql);
$selectStatement->bindParam(':player_id',$player_id);
$selectStatement->execute();
$result = $selectStatement->fetch(PDO::FETCH_ASSOC);

//alle teams ophalen om in een array te zetten
$selectTeamsSql = 'SELECT team_id, club FROM teams';
$selectTeamsStatement = $pdo->prepare($selectTeamsSql);
$selectTeamsStatement->execute();

?>
<form action="php/edit_player.php" method="POST" enctype="multipart/form-data" >
    <input type="hidden" name="player_id" id="player_id" value="<?php echo $result['player_id'] ?>">
    <fieldset class="fieldsetplayerdetails">
        <legend class="legendplayerdetails">Speler aanpassen</legend>
        <div><img class="imageplayerdeatails" src="data:image/jpeg;base64,<?php echo base64_encode($result['image'])?>" alt="Spelersfoto"></div>
        <input class="editplayerimagebutton" type="file" name="image" accept="image/*">
        <label class="labelplayerdetails" for="firstname">Voornaam</label>
        <input class="textboxplayerdetails" type="text" name="firstname" id="firstname" value="<?php echo isset($_SESSION['form_values']['firstname']) ? $_SESSION['form_values']['firstname'] : $result['firstname']?>">
        <br>
        <label class="labelplayerdetails"  for="infix">Tussenvoegsel</label>
        <input class="textboxplayerdetails" type="text" name="infix" id="infix" value="<?php echo isset($_SESSION['form_values']['infix']) ? $_SESSION['form_values']['infix'] : $result['infix']?>">
        <br>
        <label class="labelplayerdetails"  for="lastname">Achternaam</label>
        <input class="textboxplayerdetails" type="text" name="lastname" id="lastname" value="<?php echo isset($_SESSION['form_values']['lastname']) ? $_SESSION['form_values']['lastname'] : $result['lastname']?>">
        <br>
        <label class="labelplayerdetails"  for="birthdate">Geboortedatum</label>
        <input class="textboxplayerdetails" type="date" name="birthdate" id="birthdate" value="<?php echo isset($_SESSION['form_values']['birthdate']) ? $_SESSION['form_values']['birthdate'] : $result['birthdate']?>">
        <br>
        <label class="labelplayerdetails"  for="leg_id">Favoriete been</label>
        <select class="textboxplayerdetails" name="leg_id" id="leg_id">
            <option value="1" <?php echo isset($_SESSION['form_values']['leg_id']) && $_SESSION['form_values']['leg_id'] == '1' ? 'selected' : ($result['leg_id'] == '1' ? 'selected' : '')?>>Links</option>
            <option value="2" <?php echo isset($_SESSION['form_values']['leg_id']) && $_SESSION['form_values']['leg_id'] == '2' ? 'selected' : ($result['leg_id'] == '2' ? 'selected' : '')?>>Rechts</option>
        </select>
        <br>
        <label class="labelplayerdetails"  for="position_id">Favoriete positie</label>
        <select class="textboxplayerdetails" name="position_id" id="position_id">
            <option value="1" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '1' ? 'selected' : ($result['position_id'] == '1' ? 'selected' : '') ?>>Keeper</option>
            <option value="2" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '2' ? 'selected' : ($result['position_id'] == '2' ? 'selected' : '') ?>>Linksachter</option>
            <option value="3" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '3' ? 'selected' : ($result['position_id'] == '3' ? 'selected' : '') ?>>Linker centrale verdediger</option>
            <option value="4" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '4' ? 'selected' : ($result['position_id'] == '4' ? 'selected' : '') ?>>Rechter centrale verdediger</option>
            <option value="5" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '5' ? 'selected' : ($result['position_id'] == '5' ? 'selected' : '') ?>>Rechtsachter</option>
            <option value="6" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '6' ? 'selected' : ($result['position_id'] == '6' ? 'selected' : '') ?>>Linkshalf</option>
            <option value="7" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '7' ? 'selected' : ($result['position_id'] == '7' ? 'selected' : '') ?>>Centrale verdedigender middenvelder</option>
            <option value="8" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '8' ? 'selected' : ($result['position_id'] == '8' ? 'selected' : '') ?>>Centrale aanvallende middenvelder</option>
            <option value="9" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '9' ? 'selected' : ($result['position_id'] == '9' ? 'selected' : '') ?>>Rechtshalf</option>
            <option value="10" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '10' ? 'selected' : ($result['position_id'] == '10' ? 'selected' : '') ?>>Linksbuiten</option>
            <option value="11" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '11' ? 'selected' : ($result['position_id'] == '11' ? 'selected' : '') ?>>Spits</option>
            <option value="12" <?php echo isset($_SESSION['form_values']['position_id']) && $_SESSION['form_values']['position_id'] == '12' ? 'selected' : ($result['position_id'] == '12' ? 'selected' : '') ?>>Rechtsbuiten</option>
        </select>
        <br>
        <label class="labelplayerdetails"  for="jerseynumber">Rugnummer</label>
        <input class="textboxplayerdetails" type="number" name="jerseynumber" id="jerseynumber" value="<?php echo isset($_SESSION['form_values']['jerseynumber']) ? $_SESSION['form_values']['jerseynumber'] : $result['jerseynumber']?>">
        <br>
        <label class="labelplayerdetails"  for="club">Team</label>
        <select class="textboxplayerdetails" name="club" id="club">
            <option></option>
            <?php while ($team = $selectTeamsStatement->fetch()){ ?>
                <option value='<?= $team['team_id'] ?>'<?php if ($team['team_id'] == $result['team_id']) echo 'selected';'' ?>><?= $team['club']?></option>
            <?php } ?>
        </select>
        <br><br>
        <input type="reset" class="resetplayerinfo">
        <input class="editplayer" type="submit" value="Aanpassen">
    </fieldset>
</form>