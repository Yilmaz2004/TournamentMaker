<?php

$tournament_id = $_GET['tournament_id'];
$brackettype_U = 'U';
$brackettype_L1 = 'L1';
$brackettype_L2 = 'L2';
$brackettype_F = 'F';
$matchround = 0;
$is_deleted = '1';
$role_id = '2';

//select teams from tournament to get team amount
$selectTeamAmountSql = 'SELECT * FROM tournaments_teams WHERE tournament_id = :tournament_id';
$selectTeamAmountStatement = $pdo->prepare($selectTeamAmountSql);
$selectTeamAmountStatement->bindParam(':tournament_id',$tournament_id);
$selectTeamAmountStatement->execute();

$currentmatchround = $selectTeamAmountStatement->rowCount() / 2;

//select tournamentname
$selectnameSql = 'SELECT tournaments.name FROM tournaments WHERE tournaments.tournament_id = :tournament_id';
$selectnameStatement = $pdo->prepare($selectnameSql);
$selectnameStatement->bindParam(':tournament_id',$tournament_id);
$selectnameStatement->execute();
$resultname = $selectnameStatement->fetch(PDO::FETCH_ASSOC);

//select all referees
$selectRefereeSql = 'SELECT user_id, firstname FROM users WHERE role_id = :role_id AND is_deleted != :is_deleted';
$selectRefereeStatement = $pdo->prepare($selectRefereeSql);
$selectRefereeStatement->bindParam(':role_id', $role_id);
$selectRefereeStatement->bindParam(':is_deleted', $is_deleted);
$selectRefereeStatement->execute();
$referees = $selectRefereeStatement->fetchAll(PDO::FETCH_ASSOC);

$selectTournamentRefereesSql = 'SELECT user_id FROM tournaments_referees WHERE tournament_id = :tournament_id';
$selectTournamentRefereesStatement = $pdo->prepare($selectTournamentRefereesSql);
$selectTournamentRefereesStatement->bindParam(':tournament_id', $tournament_id);
$selectTournamentRefereesStatement->execute();
$tournamentReferees = $selectTournamentRefereesStatement->fetchAll(PDO::FETCH_COLUMN);


?>

<h1 class="DEtournamentname"><?php echo $resultname['name']?></h1>
<br>
<p class="editrefereesDE">Scheidsrechters</p>
<form action="php/edit_refereesDEtournament.php" method="POST">
    <input type="hidden" name="tournament_id" value="<?php echo $tournament_id ?>">
    <input type="hidden" name="bracket" value="<?php echo $brackettype_U ?>">
<div id="select2-container-editreferees">
    <select class="form-select" name="referees[]" id="multiple-select-field-editreferees" data-placeholder="Scheidsrechter(s) kiezen..." multiple >
        <?php foreach ($referees as $referee) { ?>
            <option value="<?=($referee['user_id']) ?>" <?= in_array($referee['user_id'], $tournamentReferees) ? 'selected' : '' ?>>
                <?=($referee['firstname']) ?>
            </option>
        <?php } ?>
    </select>
</div>
    <button type="submit" class="bteditreferees"><i class="bi bi-pencil"></i></button>
</form>
<button class="btdeleteDEtournament" onclick="return confirmDelete()">Toernooi verwijderen</button>
<br>
<button class="btUpperbracket">Upperbracket</button>
<br>
<button class="btLowerbracket" onclick="location.href='index.php?page=view_lowerbracketDEtournament_admin&tournament_id=<?php echo $tournament_id ?>;'">Lowerbracket</button>



<?php
//check if there is only one match (which should be the finale)
$checkSql = 'SELECT * FROM tournamentmatch WHERE tournament_id = :tournament_id';
$checkStatement = $pdo->prepare($checkSql);
$checkStatement->bindParam(':tournament_id',$tournament_id);
$checkStatement->execute();

if($checkStatement->rowCount() == 1)
{
    //Finale
    $currentmatchround = '1';
    echo'<fieldset class="fieldsetDEtournament">';
    echo'<legend class="legendmatchround">Toernooi finale</legend>';

    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id   
              FROM tournamentmatch as tm
              LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
              LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
              WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_F);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();

    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
                 <div id="matchinfo">
                     <p class="team1"> ' . $result['team1_club'] . ' </p>
                     <p class="VS">VS</p>
                     <p class="team2"> ' . $result['team2_club'] . '</p>
                     <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" readonly>
                     <p class="scoredivider">:</p>
                     <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" readonly>
                 </div>
             </form>';
    }
    echo'</fieldset>';

}


else
{
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';

    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Voorronde</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Kwart finale</legend>';
    }
    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround">Halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Upperbracket finale</legend>';
    }


    //select all matches that are in the upper bracket and in first rounds
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id 
                  FROM tournamentmatch as tm
                  LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
                  LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
                  WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_U);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();


    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" readonly>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" readonly>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>


<!--volgende ronde-->
<?php
$currentmatchround = $currentmatchround/2;
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';
    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Voorronde</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Kwart finale</legend>';
    }
    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround">Halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Upperbracket finale</legend>';
    }

    //select all matches that are in the upper bracket and in second round
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id  
              FROM tournamentmatch as tm
              LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
              LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
              WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_U);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();


    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" readonly>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" readonly>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>



<!--volgende ronde-->
<?php
$currentmatchround = $currentmatchround/2;
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';
    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Voorronde</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Kwart finale</legend>';
    }
    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround">Halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Upperbracket finale</legend>';
    }

    //select all matches that are in the upper bracket and in second round
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id   
              FROM tournamentmatch as tm
              LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
              LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
              WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_U);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();


    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" readonly>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" readonly>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>


<!--volgende ronde-->
<?php
$currentmatchround = $currentmatchround/2;
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';
    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Voorronde</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Kwart finale</legend>';
    }
    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround">Halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Upperbracket finale</legend>';
    }

    //select all matches that are in the upper bracket and in second round
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id   
              FROM tournamentmatch as tm
              LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
              LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
              WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_U);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();


    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" readonly>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" readonly>
             </div>
             </form>';
    }

    echo'</fieldset>';
}

//Finale
$currentmatchround = '1';
echo'<fieldset class="fieldsetDEtournament">';
echo'<legend class="legendmatchround">Toernooi finale</legend>';

$selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id   
              FROM tournamentmatch as tm
              LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
              LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
              WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
$selectStatement->bindParam(':brackettype',$brackettype_F);
$selectStatement->bindParam(':matchround',$currentmatchround);
$selectStatement->execute();

while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
{
    echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" readonly>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" readonly>
             </div>
             </form>';
}
echo'</fieldset>';
}
?>

<script>
    $( '#multiple-select-field-editreferees' ).select2( {
        theme: "bootstrap-5",
        placeholder: $( this ).data( 'placeholder' ),
        closeOnSelect: false,
    } );
</script>


<script>
    function confirmDelete()
    {
        var confirmResult = confirm('Weet je zeker dat je dit toernooi wilt verwijderen?')
        if(confirmResult)
        {
            location.href='php/deleteDEtournament.php?tournament_id=' + <?php echo $tournament_id ?>
        }
        return confirmResult;
    }

</script>