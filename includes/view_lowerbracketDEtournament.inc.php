<?php

$tournament_id = $_GET['tournament_id'];
$brackettype_U = 'U';
$brackettype_L1 = 'L1';
$brackettype_L2 = 'L2';
$brackettype_F = 'F';
$matchround = 0;


//select teams from tournament to get team amount
$selectTeamAmountSql = 'SELECT * FROM tournaments_teams WHERE tournament_id = :tournament_id';
$selectTeamAmountStatement = $pdo->prepare($selectTeamAmountSql);
$selectTeamAmountStatement->bindParam(':tournament_id',$tournament_id);
$selectTeamAmountStatement->execute();
$currentmatchround = $selectTeamAmountStatement->rowCount() / 2;

//select tournamentname
$selectnameSql = 'SELECT tournaments.name, GROUP_CONCAT(users.firstname SEPARATOR \', \') as firstname 
                  FROM tournaments 
                  LEFT JOIN tournaments_referees ON tournaments.tournament_id = tournaments_referees.tournament_id
                  LEFT JOIN users ON tournaments_referees.user_id = users.user_id
                  WHERE tournaments.tournament_id = :tournament_id';
$selectnameStatement = $pdo->prepare($selectnameSql);
$selectnameStatement->bindParam(':tournament_id',$tournament_id);
$selectnameStatement->execute();
$resultname = $selectnameStatement->fetch(PDO::FETCH_ASSOC);
?>
<h1 class="DEtournamentname"><?php echo $resultname['name']?></h1>
<br>
<h5 class="DEtournamentlabelreferees">Scheidsrechters:</h5>
<div class="Detournamentreferees"><?php echo $resultname['firstname'] ?></div>
<button class="btUpperbracket" onclick="location.href='index.php?page=view_upperbracketDEtournament&tournament_id=<?php echo $tournament_id ?>;'">Upperbracket</button>
<br>
<button class="btLowerbracket" onclick="location.href='index.php?page=view_lowerbracketDEtournament&tournament_id=<?php echo $tournament_id ?>;'">Lowerbracket</button>


<?php
$currentmatchround = $currentmatchround /2;
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';


    //select all matches that are in the upper bracket and in first rounds
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id   
                  FROM tournamentmatch as tm
                  LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
                  LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
                  WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_L1);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();

    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Voorronde lowerbracket</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Eerste ronde kwart finale</legend>';
    }

    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround">Eerste ronde halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Eerste ronde lowerbracket Finale</legend>';
    }


    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <input type="hidden" name="bracket" value="L" >
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <input type="hidden" name="match_id" value="' . $result['tournamentmatch_id'] . '">
             <input type="hidden" name="matchround" value="' . $result['matchround'] . '">
             <input type="hidden" name="brackettype" value="' . $result['brackettype'] . '">
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <input type="hidden" name="team1" value="' . $result['team1_id'] . '">
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input type="hidden" name="team2" value="' . $result['team2_id'] . '">
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" required>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" required>
             <button class="btsubmitscore" type="submit"><i class="bi bi-check-square"></i></button>
             </div>
             </form>';
    }

    echo'</fieldset>';
}
?>

<!--Extra round lowerbracket-->
<?php
if($currentmatchround > 0.5)
{
echo'<fieldset class="fieldsetDEtournament">';

    //select all matches that are in the upper bracket and in first rounds
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id  
    FROM tournamentmatch as tm
    LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
    LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
    WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_L2);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();

    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Extra voorronde lowerbracket</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Tweede ronde kwart finale</legend>';
    }
    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround"> Tweede ronde halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Tweede ronde lowerbracket Finale</legend>';
    }

    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <input type="hidden" name="bracket" value="L" >
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <input type="hidden" name="match_id" value="' . $result['tournamentmatch_id'] . '">
             <input type="hidden" name="matchround" value="' . $result['matchround'] . '">
             <input type="hidden" name="brackettype" value="' . $result['brackettype'] . '">
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <input type="hidden" name="team1" value="' . $result['team1_id'] . '">
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input type="hidden" name="team2" value="' . $result['team2_id'] . '">
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" required>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" required>
             <button class="btsubmitscore" type="submit"><i class="bi bi-check-square"></i></button>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>


<?php
$currentmatchround = $currentmatchround /2;
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';


    //select all matches that are in the upper bracket and in first rounds
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id   
                  FROM tournamentmatch as tm
                  LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
                  LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
                  WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_L1);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();

    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Voorronde lowerbracket</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Eerste ronde kwart finale</legend>';
    }

    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround">Eerste ronde halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Eerste ronde lowerbracket Finale</legend>';
    }

    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <input type="hidden" name="bracket" value="L" >
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <input type="hidden" name="match_id" value="' . $result['tournamentmatch_id'] . '">
             <input type="hidden" name="matchround" value="' . $result['matchround'] . '">
             <input type="hidden" name="brackettype" value="' . $result['brackettype'] . '">
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <input type="hidden" name="team1" value="' . $result['team1_id'] . '">
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input type="hidden" name="team2" value="' . $result['team2_id'] . '">
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" required>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" required>
             <button class="btsubmitscore" type="submit"><i class="bi bi-check-square"></i></button>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>

    <!--Extra round lowerbracket-->
<?php
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';

    //select all matches that are in the upper bracket and in first rounds
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id  
    FROM tournamentmatch as tm
    LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
    LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
    WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_L2);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();

    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Extra voorronde lowerbracket</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Tweede ronde kwart finale</legend>';
    }
    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround"> Tweede ronde halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Tweede ronde lowerbracket Finale</legend>';
    }

    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <input type="hidden" name="bracket" value="L" >
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <input type="hidden" name="match_id" value="' . $result['tournamentmatch_id'] . '">
             <input type="hidden" name="matchround" value="' . $result['matchround'] . '">
             <input type="hidden" name="brackettype" value="' . $result['brackettype'] . '">
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <input type="hidden" name="team1" value="' . $result['team1_id'] . '">
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input type="hidden" name="team2" value="' . $result['team2_id'] . '">
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" required>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" required>
             <button class="btsubmitscore" type="submit"><i class="bi bi-check-square"></i></button>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>


<?php
$currentmatchround = $currentmatchround /2;
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';

    //select all matches that are in the upper bracket and in first rounds
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id   
                  FROM tournamentmatch as tm
                  LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
                  LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
                  WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_L1);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();

    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Voorronde lowerbracket</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Eerste ronde kwart finale</legend>';
    }

    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround">Eerste ronde halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Eerste ronde lowerbracket Finale</legend>';
    }

    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <input type="hidden" name="bracket" value="L" >
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <input type="hidden" name="match_id" value="' . $result['tournamentmatch_id'] . '">
             <input type="hidden" name="matchround" value="' . $result['matchround'] . '">
             <input type="hidden" name="brackettype" value="' . $result['brackettype'] . '">
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <input type="hidden" name="team1" value="' . $result['team1_id'] . '">
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input type="hidden" name="team2" value="' . $result['team2_id'] . '">
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" required>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" required>
             <button class="btsubmitscore" type="submit"><i class="bi bi-check-square"></i></button>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>

<!--Extra round lowerbracket-->
<?php
if($currentmatchround > 0.5)
{
    echo'<fieldset class="fieldsetDEtournament">';

    //select all matches that are in the upper bracket and in first rounds
    $selectSql = 'SELECT t1.club AS team1_club, t2.club AS team2_club, t1.logo as team1_logo, t2.logo as team2_logo, tm.team1_id, tm.team2_id, tm.score1, tm.score2, tm.matchround, tm.brackettype, tm.tournamentmatch_id  
    FROM tournamentmatch as tm
    LEFT JOIN teams as t1 ON t1.team_id = tm.team1_id
    LEFT JOIN teams as t2 ON t2.team_id = tm.team2_id
    WHERE tournament_id = :tournamentmatch_id AND brackettype = :brackettype AND matchround = :matchround';
    $selectStatement = $pdo->prepare($selectSql);
    $selectStatement->bindParam(':tournamentmatch_id',$tournament_id);
    $selectStatement->bindParam(':brackettype',$brackettype_L2);
    $selectStatement->bindParam(':matchround',$currentmatchround);
    $selectStatement->execute();

    if($currentmatchround > 4)
    {
        echo'<legend class="legendmatchround">Extra voorronde lowerbracket</legend>';
    }
    else if($currentmatchround == 4)
    {
        echo'<legend class="legendmatchround">Tweede ronde kwart finale</legend>';
    }
    else if($currentmatchround == 2)
    {
        echo'<legend class="legendmatchround"> Tweede ronde halve finale</legend>';
    }
    else if($currentmatchround == 1)
    {
        echo'<legend class="legendmatchround">Tweede ronde lowerbracket Finale</legend>';
    }

    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo'<form action="php/confirmresultsDEtournament.php" method="POST"> 
             <div id="matchinfo">
             <input type="hidden" name="bracket" value="L" >
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <input type="hidden" name="match_id" value="' . $result['tournamentmatch_id'] . '">
             <input type="hidden" name="matchround" value="' . $result['matchround'] . '">
             <input type="hidden" name="brackettype" value="' . $result['brackettype'] . '">
             <input type="hidden" name="tournament_id" value="' . $tournament_id . '">
             <p class="team1"> ' . $result['team1_club'] . ' </p>
             <input type="hidden" name="team1" value="' . $result['team1_id'] . '">
             <p class="VS">VS</p>
             <p class="team2"> ' . $result['team2_club'] . '</p>
             <input type="hidden" name="team2" value="' . $result['team2_id'] . '">
             <input class="score1" type="number" name="score1" value="' . $result['score1'] . '" required>
             <p class="scoredivider">:</p>
             <input class="score2" type="number" name="score2" value="' . $result['score2'] . '" required>
             <button class="btsubmitscore" type="submit"><i class="bi bi-check-square"></i></button>
             </div>
             </form>';
    }
    echo'</fieldset>';
}
?>
