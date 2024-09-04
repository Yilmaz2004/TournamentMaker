<?php

//select all teams
$is_deleted = '1';

$selectSql = 'SELECT * FROM teams WHERE is_deleted != :is_deleted';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':is_deleted',$is_deleted);
$selectStatement->execute();

?>

<h1 class="headerDE">Double elimination toernooi aanmaken</h1>
<p class="textcreateDE">Toernooi naam</p>
<form action="php/add_DEtournament.php" method="POST">
    <input class="textboxtournamentname" type="text" name="tournamentname" id="tournamentname" placeholder="Toernooi naam..." required><br>
    <p class="labelstartdateDE">Startdatum</p>
    <input class="textboxstartdateDE" type="date" name="startdate" id="startdate" required><br>
    <p class="teamstextcreateDE">Kies 2, 4, 8, 16 of 32 teams.</p>
    <div id="select2-container">
        <select class="form-select" name="teams[]" id="multiple-select-field" data-placeholder="Teams kiezen..." multiple required>
            <?php while($team = $selectStatement->fetch()) {?>
                <option value="<?= $team['team_id']?>"><?= $team['club'] ?></option>
            <?php } ?>
        </select>
    </div>
    <br>
    <input class="buttonsubmitteams" type="submit" value="Maak toernooi">

</form>


<script>
    $( '#multiple-select-field' ).select2( {
        theme: "bootstrap-5",
        placeholder: $( this ).data( 'placeholder' ),
        closeOnSelect: false,
    } );


</script>