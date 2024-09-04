<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}

$player_id = $_GET['player_id'];

//select player information
$selectSql = 'SELECT players.image, players.firstname, players.infix, players.lastname, players.birthdate, legs.leg, positions.position, players.jerseynumber, teams.club
              FROM players
              LEFT JOIN legs ON players.leg_id = legs.leg_id
              LEFT JOIN positions ON players.position_id = positions.position_id
              LEFT JOIN teams ON players.team_id = teams.team_id
              WHERE players.player_id = :player_id';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':player_id',$player_id);
$selectStatement->execute();
$result = $selectStatement->fetch(PDO::FETCH_ASSOC);


?>

<fieldset class="fieldsetplayerdetails">
    <legend class="legendplayerdetails">Speler details</legend>
    <div><img class="imageplayerdeatails" src="data:image/jpeg;base64,<?php echo base64_encode( $result['image'])?>" alt="Spelersfoto"></div>
    <label class="labelplayerdetails" for="firstname">Voornaam</label>
    <input class="textboxplayerdetails" type="text" name="firstname" id="firstname" readonly value="<?php echo $result['firstname']?>">
    <br>
    <label class="labelplayerdetails"  for="infix">Tussenvoegsel</label>
    <input class="textboxplayerdetails" type="text" name="infix" id="infix" readonly value="<?php echo $result['infix']?>">
    <br>
    <label class="labelplayerdetails"  for="lastname">Achternaam</label>
    <input class="textboxplayerdetails" type="text" name="lastname" id="lastname" readonly value="<?php echo $result['lastname']?>">
    <br>
    <label class="labelplayerdetails"  for="birthdate">Geboortedatum</label>
    <input class="textboxplayerdetails" type="text" name="birthdate" id="birthdate" readonly value="<?php echo $result['birthdate']?>">
    <br>
    <label class="labelplayerdetails"  for="leg">Favoriete been</label>
    <input class="textboxplayerdetails" type="text" name="leg" id="leg" readonly value="<?php echo $result['leg']?>">
    <br>
    <label class="labelplayerdetails"  for="position">Favoriete positie</label>
    <input class="textboxplayerdetails" type="text" name="position" id="position" readonly value="<?php echo $result['position']?>">
    <br>
    <label class="labelplayerdetails"  for="jerseynumber">Rugnummer</label>
    <input class="textboxplayerdetails" type="text" name="jerseynumber" id="jerseynumber" readonly value="<?php echo $result['jerseynumber']?>">
    <br>
    <label class="labelplayerdetails"  for="club">Team</label>
    <input class="textboxplayerdetails" type="text" name="club" id="club" readonly value="<?php echo $result['club']?>">

</fieldset>
