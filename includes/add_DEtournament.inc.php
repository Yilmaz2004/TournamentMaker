<?php
$is_deleted = '1';
$role_id = '2';

//select all teams
$selectTeamSql = 'SELECT * FROM teams WHERE is_deleted != :is_deleted';
$selectTeamStatement = $pdo->prepare($selectTeamSql);
$selectTeamStatement->bindParam(':is_deleted',$is_deleted);
$selectTeamStatement->execute();

//select all referees
$selectRefereeSql = 'SELECT * FROM users WHERE role_id = :role_id AND is_deleted != :is_deleted';
$selectRefereeStatement = $pdo->prepare($selectRefereeSql);
$selectRefereeStatement->bindParam(':role_id',$role_id);
$selectRefereeStatement->bindParam(':is_deleted',$is_deleted);
$selectRefereeStatement->execute();
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
            <?php while($team = $selectTeamStatement->fetch()) {?>
            <option value="<?= $team['team_id']?>"><?= $team['club'] ?></option>
            <?php } ?>
        </select>
    </div>
    <p class="teamstextcreateDE">Scheidsrechter</p>
    <div id="select2-container-referees">
        <select class="form-select" name="referees[]" id="multiple-select-field-referees" data-placeholder="Scheidsrechter(s) kiezen..." multiple required>
            <?php while($referee = $selectRefereeStatement->fetch()) {?>
                <option value="<?= $referee['user_id']?>"><?= $referee['firstname'] ?></option>
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

    $( '#multiple-select-field-referees' ).select2( {
        theme: "bootstrap-5",
        placeholder: $( this ).data( 'placeholder' ),
        closeOnSelect: false,
    } );
</script>