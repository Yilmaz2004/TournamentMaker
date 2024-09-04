<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}
?>



<form action="php/add_stadium.php" method="post" enctype="multipart/form-data">
    <fieldset class="fieldsetstadiums">
        <legend class="legendaddstadium">Stadion toevoegen</legend>
        <div class="imagestadiumdiv">
            <img class="" src="data:image/jpeg;base64," alt="Afbeelding stadion">
        </div>
        <input class="editstadiumimagebutton" type="file" name="image" accept="image/*">
        <br>
        <label class="labelstadium" for="name">Naam</label>
        <input class="textboxstadium" type="text" name="name" id="name" required>
        <br>
        <label class="labelstadium" for="seats">Stoelen</label>
        <input class="textboxstadium" type="number" name="seats" id="seats" required>
        <br>
        <label class="labelstadium" for="address">Adres</label>
        <input class="textboxstadium" type="text" name="address" id="address" required>
        <br>
        <input class="resetstadiuminfo" type="reset">
        <input class="editstadium" type="submit" value="Toevoegen">
    <fieldset>
</form>
