<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}

include'connection.php';

$stadium_id = $_GET['stadium_id'];

//get stadium info based on ID
$selectSql = 'SELECT * FROM stadiums WHERE stadium_id = :stadium_id';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':stadium_id',$stadium_id);
$selectStatement->execute();
$result = $selectStatement->fetch(PDO::FETCH_ASSOC);

?>



<form action="php/edit_stadium.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="stadium_id" id="stadium_id" value="<?php echo $result['stadium_id'] ?>">
    <fieldset class="fieldsetstadiums">
        <legend class="legendaddstadium">Stadion aanpassen</legend>
        <div class="imagestadiumdiv">
            <img class="imagestadium" src="data:image/jpeg;base64,<?php echo base64_encode($result['image']) ?>" alt="Afbeelding stadion">
        </div>
        <input class="editstadiumimagebutton" type="file" name="image" accept="image/*">
        <br>
        <label class="labelstadium" for="name">Naam</label>
        <input class="textboxstadium" type="text" name="name" id="name" value="<?php echo isset($_SESSION['form_values']['name']) ? $_SESSION['form_values']['name'] : $result['name'] ?>" required>
        <br>
        <label class="labelstadium" for="seats">Stoelen</label>
        <input class="textboxstadium" type="number" name="seats" id="seats" value="<?php echo isset($_SESSION['form_values']['seats']) ? $_SESSION['form_values']['seats'] : $result['seats'] ?>" required>
        <br>
        <label class="labelstadium" for="address">Adres</label>
        <input class="textboxstadium" type="text" name="address" id="address" value="<?php echo isset($_SESSION['form_values']['address']) ? $_SESSION['form_values']['address'] : $result['address'] ?>" required>
        <br>
        <input class="resetstadiuminfo" type="reset">
        <input class="editstadium" type="submit" value="Aanpassen">
        <fieldset>
</form>
