<?php

include'../connection.php';
session_start();

$team1 = $_POST['team1'];
$team2 = $_POST['team2'];
$scoreteam1 = $_POST['score1'];
$scoreteam2 = $_POST['score2'];
$bracket = $_POST['bracket'];
$match_id = $_POST['match_id'];
$matchround = $_POST['matchround'];
$brackettype = $_POST['brackettype'];
$tournament_id = $_POST['tournament_id'];

//error if there is only one team
if($team1 == '' || $team2 == '' || $team1 == $team2 )
{
    $_SESSION['error'] = 'Deze wedstrijd kan nog niet ingevuld worden!';
    if($bracket == 'U')
    {
        header('Location:../index.php?page=view_upperbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . ' ');
        exit();
    }
    else
    {
        header('Location:../index.php?page=view_lowerbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . '');
        exit();
    }
}

//error if scores send are negative numbers
if($scoreteam1 < 0 || $scoreteam2 < 0)
{
     $_SESSION['error'] = 'Ingevulde scores mogen niet negatief zijn!';
     if($bracket == 'U')
     {
        header('Location:../index.php?page=view_upperbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . ' ');
        exit();
     }
     else
     {
         header('Location:../index.php?page=view_lowerbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . '');
         exit();
     }
}

//error if scores are the same
if($scoreteam1 == $scoreteam2)
{
    $_SESSION['error'] = 'Gelijkspel is niet mogelijk in een elimination toernooi!';
    if($bracket == 'U')
    {
        header('Location:../index.php?page=view_upperbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . ' ');
        exit();
    }
    else
    {
        header('Location:../index.php?page=view_lowerbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . '');
        exit();
    }
}

//error if ref tries to update scores but the next match has already been played
//select which matches are next
$selectnextmatchSql = 'SELECT tournamentmatch_id FROM tournamentmatch 
                       WHERE (w1 = :match_id OR w2 = :match_id OR l1 = :match_id OR l2 = :match_id) 
                       AND tournament_id = :tournament_id';
$selectnextmatchStatement = $pdo->prepare($selectnextmatchSql);
$selectnextmatchStatement->bindParam(':match_id',$match_id);
$selectnextmatchStatement->bindParam(':tournament_id',$tournament_id);
$selectnextmatchStatement->execute();
$resultnextmatch = $selectnextmatchStatement->fetchAll(PDO::FETCH_ASSOC);
//$nextmatch_id = $resultnextmatch['tournamentmatch_id'];

//check if the scores are filled in
foreach ($resultnextmatch as $nextmatch) {
    $nextmatch_id = $nextmatch['tournamentmatch_id'];

    $checkscoresSql = 'SELECT score1, score2 FROM tournamentmatch WHERE tournamentmatch_id = :nextmatch_id 
                                                              AND tournament_id = :tournament_id';
    $checkscoresStatement = $pdo->prepare($checkscoresSql);
    $checkscoresStatement->bindParam(':nextmatch_id', $nextmatch_id);
    $checkscoresStatement->bindParam(':tournament_id', $tournament_id);
    $checkscoresStatement->execute();
    $resultcheckscores = $checkscoresStatement->fetch(PDO::FETCH_ASSOC);

//execute the error
    if ($resultcheckscores['score1'] != NULL || $resultcheckscores['score2'] != NULL) {
        $_SESSION['error'] = 'Deze match kan niet meer aangepast worden, volgende wedstrijd is al gespeeld!';
        if ($bracket == 'U') {
            header('Location:../index.php?page=view_upperbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . ' ');
            exit();
        } else {
            header('Location:../index.php?page=view_lowerbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . '');
            exit();
        }
    }
}

//insert score into database
$insertScoreSql = 'UPDATE tournamentmatch SET score1 = :score1, score2 = :score2 
                   WHERE tournament_id = :tournament_id AND team1_id = :team1 AND team2_id = :team2 AND matchround = :matchround';
$insertScoreStatement = $pdo->prepare($insertScoreSql);
$insertScoreStatement->bindParam(':score1',$scoreteam1);
$insertScoreStatement->bindParam(':score2',$scoreteam2);
$insertScoreStatement->bindParam(':tournament_id',$tournament_id);
$insertScoreStatement->bindParam(':team1',$team1);
$insertScoreStatement->bindParam(':team2',$team2);
$insertScoreStatement->bindParam(':matchround',$matchround);
$insertScoreStatement->execute();

//who is the winner and who is the loser
if($scoreteam1 > $scoreteam2)
{
    $winner = $team1;
    $loser = $team2;
}
else
{
    $winner = $team2;
    $loser = $team1;
}

//check next match for the winner
$checkwinnermatchSql = 'SELECT tournamentmatch_id FROM tournamentmatch WHERE (w1 = :match_id OR w2 = :match_id) 
                                                                       AND tournament_id = :tournament_id';
$checkwinnermatchStatement = $pdo->prepare($checkwinnermatchSql);
$checkwinnermatchStatement->bindParam(':match_id',$match_id);
$checkwinnermatchStatement->bindParam('tournament_id',$tournament_id);
$checkwinnermatchStatement->execute();
$resultW = $checkwinnermatchStatement->fetch(PDO::FETCH_ASSOC);
$winnermatch_id = $resultW['tournamentmatch_id'];


//check next match for loser
$checklosermatchSql = 'SELECT tournamentmatch_id FROM tournamentmatch WHERE (l1 = :match_id OR l2 = :match_id)
                                                                      AND tournament_id = :tournament_id';
$checklosermatchStatement = $pdo->prepare($checklosermatchSql);
$checklosermatchStatement->bindParam(':match_id',$match_id);
$checklosermatchStatement->bindParam('tournament_id',$tournament_id);
$checklosermatchStatement->execute();
$resultL = $checklosermatchStatement->fetch(PDO::FETCH_ASSOC);
$losermatch_id = $resultL['tournamentmatch_id'];


//check if id was found in: w1, w2, l1 or l2
$selectwinnercolumnSql = 'SELECT w1, w2 FROM tournamentmatch WHERE tournamentmatch_id = :winnermatch_id 
                                                              AND tournament_id = :tournament_id
                                                              AND w1 = :match_id OR w2 = :match_id';
$selectwinnercolumnStatement = $pdo->prepare($selectwinnercolumnSql);
$selectwinnercolumnStatement->bindParam(':winnermatch_id',$winnermatch_id);
$selectwinnercolumnStatement->bindParam(':tournament_id',$tournament_id);
$selectwinnercolumnStatement->bindParam(':match_id',$match_id);
$selectwinnercolumnStatement->execute();
$resultwinnercolumn = $selectwinnercolumnStatement->fetch(PDO::FETCH_ASSOC);

$selectlosercolumnSql = 'SELECT l1, l2 FROM tournamentmatch WHERE tournamentmatch_id = :losermatch_id
                                                            AND tournament_id = :tournament_id
                                                            AND l1 = :match_id OR l2 = :match_id';
$selectlosercolumnStatement = $pdo->prepare($selectlosercolumnSql);
$selectlosercolumnStatement->bindParam(':losermatch_id',$losermatch_id);
$selectlosercolumnStatement->bindParam(':tournament_id',$tournament_id);
$selectlosercolumnStatement->bindParam(':match_id',$match_id);
$selectlosercolumnStatement->execute();
$resultlosercolumn = $selectlosercolumnStatement->fetch(PDO::FETCH_ASSOC);

//update the next winner match
if($resultwinnercolumn['w1'] == $match_id)
{
    $w1Sql = 'UPDATE tournamentmatch SET team1_id = :winner WHERE tournamentmatch_id = :winnermatch_id 
                                                            AND tournament_id = :tournament_id';
    $w1Statement = $pdo->prepare($w1Sql);
    $w1Statement->bindParam(':winner',$winner);
    $w1Statement->bindParam('winnermatch_id',$winnermatch_id);
    $w1Statement->bindParam('tournament_id',$tournament_id);
    $w1Statement->execute();
}
else
{
    $w2Sql = 'UPDATE tournamentmatch SET team2_id = :winner WHERE tournamentmatch_id = :winnermatch_id 
                                                            AND tournament_id = :tournament_id';
    $w2Statement = $pdo->prepare($w2Sql);
    $w2Statement->bindParam(':winner',$winner);
    $w2Statement->bindParam('winnermatch_id',$winnermatch_id);
    $w2Statement->bindParam('tournament_id',$tournament_id);
    $w2Statement->execute();
}

//update the next loser match
if($resultlosercolumn['l1'] == $match_id)
{
    $l1Sql = 'UPDATE tournamentmatch SET team1_id = :loser WHERE tournamentmatch_id = :losermatch_id
                                                           AND tournament_id = :tournament_id';
    $l1Statement = $pdo->prepare($l1Sql);
    $l1Statement->bindParam(':loser',$loser);
    $l1Statement->bindParam(':losermatch_id',$losermatch_id);
    $l1Statement->bindParam(':tournament_id',$tournament_id);
    $l1Statement->execute();
}
else
{
    $l2Sql = 'UPDATE tournamentmatch SET team2_id = :loser WHERE tournamentmatch_id = :losermatch_id
                                                           AND tournament_id = :tournament_id';
    $l2Statement = $pdo->prepare($l2Sql);
    $l2Statement->bindParam(':loser',$loser);
    $l2Statement->bindParam(':losermatch_id',$losermatch_id);
    $l2Statement->bindParam(':tournament_id',$tournament_id);
    $l2Statement->execute();
}

$_SESSION['success'] = 'Score succesvol ingevuld!';
if($bracket == 'U')
{
    header('Location:../index.php?page=view_upperbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . ' ');
}
else
{
    header('Location:../index.php?page=view_lowerbracketDEtournament&tournament_id=' . $_POST['tournament_id'] . '');
}

?>