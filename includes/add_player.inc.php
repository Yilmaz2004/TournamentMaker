<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}

//Select all teams to put in an array
$selectTeamsSql = 'SELECT team_id, club FROM teams';
$selectTeamsStatement = $pdo->prepare($selectTeamsSql);
$selectTeamsStatement->execute();

?>
<form action="php/add_player.php" method="POST" enctype="multipart/form-data" >
    <fieldset class="fieldsetplayerdetails">
        <legend class="legendplayerdetails">Speler toevoegen</legend>
        <div class="imageplayerdeatails"><img class="imageplayerdetails" src="data:image/jpeg;base64," alt="Spelersfoto"></div>
        <input class="editplayerimagebutton" type="file" name="image" accept="image/*">
        <label class="labelplayerdetails" for="firstname">Voornaam</label>
        <input class="textboxplayerdetails" type="text" name="firstname" id="firstname" required>
        <br>
        <label class="labelplayerdetails"  for="infix">Tussenvoegsel</label>
        <input class="textboxplayerdetails" type="text" name="infix" id="infix">
        <br>
        <label class="labelplayerdetails"  for="lastname">Achternaam</label>
        <input class="textboxplayerdetails" type="text" name="lastname" id="lastname" required>
        <br>
        <label class="labelplayerdetails"  for="birthdate">Geboortedatum</label>
        <input class="textboxplayerdetails" type="date" name="birthdate" id="birthdate" required>
        <br>
        <label class="labelplayerdetails"  for="leg_id">Favoriete been</label>
        <select class="textboxplayerdetails" name="leg_id" id="leg_id" required>
            <option value="1">Links</option>
            <option value="2">Rechts</option>
        </select>
        <br>
        <label class="labelplayerdetails"  for="position_id">Favoriete positie</label>
        <select class="textboxplayerdetails" name="position_id" id="position_id" required>
            <option value="1">Keeper</option>
            <option value="2">Linksachter</option>
            <option value="3">Linker centrale verdediger</option>
            <option value="4">Rechter centrale verdediger</option>
            <option value="5">Rechtsachter</option>
            <option value="6">Linkshalf</option>
            <option value="7">Centrale verdedigender middenvelder</option>
            <option value="8">Centrale aanvallende middenvelder</option>
            <option value="9">Rechtshalf</option>
            <option value="10">Linksbuiten</option>
            <option value="11">Spits</option>
            <option value="12">Rechtsbuiten</option>
        </select>
        <br>
        <label class="labelplayerdetails"  for="jerseynumber">Rugnummer</label>
        <input class="textboxplayerdetails" type="number" name="jerseynumber" id="jerseynumber" required>
        <br>
        <label class="labelplayerdetails"  for="club">Team</label>
        <select class="textboxplayerdetails" name="club" id="club" required>
            <option></option>
            <?php while ($team = $selectTeamsStatement->fetch()){ ?>
                <option value='<?= $team['team_id'] ?>'><?= $team['club']?></option>
            <?php } ?>
        </select>
        <br><br>
        <input type="reset" class="resetplayerinfo">
        <input class="editplayer" type="submit" value="Toevoegen">
    </fieldset>
</form>