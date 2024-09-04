<?php

include'../connection.php';
session_start();

$name = $_POST['tournamentname'];
$tournamenttype_id = '2';
$startdate = $_POST['startdate'];
$teams = $_POST['teams'];
$referees = $_POST['referees'];
$teamcount = count($teams);
$matchround = 0;
$brackettype_U = 'U';
$brackettype_L1 = 'L1';
$brackettype_L2 = 'L2';
$brackettype_F = 'F';

//check if tournament name is unique
$checkNameSql = 'SELECT name FROM tournaments WHERE name = :name';
$checkNameStatement = $pdo->prepare($checkNameSql);
$checkNameStatement->bindParam(':name',$name);
$checkNameStatement->execute();
$result = $checkNameStatement->fetch(PDO::FETCH_ASSOC);

if($checkNameStatement->rowCount() > 0)
{
    $_SESSION['error'] = 'Dit toernooi bestaat al!';
    header('Location:../index.php?page=add_DEtournament');
    exit();
}

if (!in_array($teamcount, [2, 4, 8, 16, 32])) {
    $_SESSION['error'] = 'Je kan alleen kiezen uit 2, 4, 8, 16 of 32 teams kiezen!';
    header('Location: ../index.php?page=add_DEtournament');
    exit();
}

else {

    //insert tournament name and type into database
    $insertTournamentSql = 'INSERT INTO tournaments (name, startdate, tournamenttype_id) VALUES (:name, :startdate, :tournamenttype_id)';
    $insertTournamentStatement = $pdo->prepare($insertTournamentSql);
    $insertTournamentStatement->bindParam(':name', $name);
    $insertTournamentStatement->bindParam(':startdate', $startdate);
    $insertTournamentStatement->bindParam(':tournamenttype_id', $tournamenttype_id);
    $insertTournamentStatement->execute();
    $tournament_id = $pdo->LastInsertId();

    //insert teams and tournament in tournaments_teams table
    $insertTournamentTeamsSql = 'INSERT INTO tournaments_teams (tournament_id, team_id) VALUES (:tournament_id, :team_id)';
    $insertTournamentTeamsStatement = $pdo->prepare($insertTournamentTeamsSql);
    foreach ($teams as $team_id) {
        $insertTournamentTeamsStatement->bindParam(':tournament_id', $tournament_id);
        $insertTournamentTeamsStatement->bindParam(':team_id', $team_id);
        $insertTournamentTeamsStatement->execute();
    }

    //insert referees and tournament in tournaments_referees table
    $insertTournamentRefereesSql = 'INSERT INTO tournaments_referees (tournament_id, user_id) VALUES (:tournament_id, :referee_id)';
    $insertTournamentRefereesStatement = $pdo->prepare($insertTournamentRefereesSql);
    foreach($referees as $user_id){
        $insertTournamentRefereesStatement->bindParam(':tournament_id',$tournament_id);
        $insertTournamentRefereesStatement->bindParam(':referee_id',$user_id);
        $insertTournamentRefereesStatement->execute();
    }

    if (in_array($teamcount, [2]))
    {
        $team1 = $teams[0];
        $team2 = $teams[1];
        $matchround = 1;

        //insert finale
        $insertMatchSql = 'INSERT INTO tournamentmatch (tournament_id, team1_id, team2_id, matchround, brackettype) 
                           VALUES (:tournament_id, :team1_id, :team2_id, :matchround, :brackettype)';
        $insertMatchStatement = $pdo->prepare($insertMatchSql);
        $insertMatchStatement->bindParam(':tournament_id', $tournament_id);
        $insertMatchStatement->bindParam(':team1_id', $team1);
        $insertMatchStatement->bindParam(':team2_id', $team2);
        $insertMatchStatement->bindParam(':matchround', $matchround);
        $insertMatchStatement->bindParam(':brackettype', $brackettype_F);
        $insertMatchStatement->execute();
    }
    else {
        //randomize the team array
        shuffle($teams);

        //make matches for the first round
        for ($i = 0; $i < $teamcount; $i += 2) {
            $team1 = $teams[$i];
            $team2 = $teams[$i + 1];
            $matchround = $teamcount / 2;


            //insert first round matches into database
            $insertMatchSql = 'INSERT INTO tournamentmatch (tournament_id, team1_id, team2_id, matchround, brackettype) 
                           VALUES (:tournament_id, :team1_id, :team2_id, :matchround, :brackettype)';
            $insertMatchStatement = $pdo->prepare($insertMatchSql);
            $insertMatchStatement->bindParam(':tournament_id', $tournament_id);
            $insertMatchStatement->bindParam(':team1_id', $team1);
            $insertMatchStatement->bindParam(':team2_id', $team2);
            $insertMatchStatement->bindParam(':matchround', $matchround);
            $insertMatchStatement->bindParam(':brackettype', $brackettype_U);
            $insertMatchStatement->execute();
        }

        //select previous matches
        $selectPreviousMatchessql = "SELECT tournamentmatch_id FROM tournamentmatch WHERE tournament_id = :tournament_id
                                                                 AND matchround = :matchround
                                                                 AND brackettype = :brackettype";
        $selectPreviousMatchesStatement = $pdo->prepare($selectPreviousMatchessql);
        $selectPreviousMatchesStatement->bindParam(':tournament_id', $tournament_id);
        $selectPreviousMatchesStatement->bindParam(':matchround', $matchround);
        $selectPreviousMatchesStatement->bindParam(':brackettype', $brackettype_U);
        $selectPreviousMatchesStatement->execute();
        $resultupper = $selectPreviousMatchesStatement->fetchAll(PDO::FETCH_ASSOC);

        $matchround = $matchround / 2;

        //insert first lowerbracket round matches
        for ($i = 0; $i < ($matchround * 2); $i += 2) {
            $insertMatchSql = 'INSERT INTO tournamentmatch (tournament_id, matchround, brackettype, l1, l2)
                           VALUES (:tournament_id, :matchround, :brackettype, :l1, :l2)';
            $insertMatchStatement = $pdo->prepare($insertMatchSql);
            $insertMatchStatement->bindParam(':tournament_id', $tournament_id);
            $insertMatchStatement->bindParam(':matchround', $matchround);
            $insertMatchStatement->bindParam(':brackettype', $brackettype_L1);
            $insertMatchStatement->bindParam(':l1', $resultupper[$i]['tournamentmatch_id']);
            $insertMatchStatement->bindParam(':l2', $resultupper[$i + 1]['tournamentmatch_id']);
            $insertMatchStatement->execute();
        }

        $matchround = $matchround * 2;

        while ($matchround != '1') {
            //select all previous matches
            $selectPreviousMatchessql = "SELECT tournamentmatch_id FROM tournamentmatch WHERE tournament_id = :tournament_id
                                                                 AND matchround = :matchround
                                                                 AND brackettype = :brackettype";
            $selectPreviousMatchesStatement = $pdo->prepare($selectPreviousMatchessql);
            $selectPreviousMatchesStatement->bindParam(':tournament_id', $tournament_id);
            $selectPreviousMatchesStatement->bindParam(':matchround', $matchround);
            $selectPreviousMatchesStatement->bindParam(':brackettype', $brackettype_U);
            $selectPreviousMatchesStatement->execute();
            $resultupper = $selectPreviousMatchesStatement->fetchAll(PDO::FETCH_ASSOC);

            //go to next round
            $matchround = $matchround / 2;

            //insert next upperbracket matches
            for ($i = 0; $i < ($matchround * 2); $i += 2) {
                $insertMatchSql = 'INSERT INTO tournamentmatch (tournament_id, matchround, brackettype, w1, w2)
                           VALUES (:tournament_id, :matchround, :brackettype, :w1, :w2)';
                $insertMatchStatement = $pdo->prepare($insertMatchSql);
                $insertMatchStatement->bindParam(':tournament_id', $tournament_id);
                $insertMatchStatement->bindParam(':matchround', $matchround);
                $insertMatchStatement->bindParam(':brackettype', $brackettype_U);
                $insertMatchStatement->bindParam(':w1', $resultupper[$i]['tournamentmatch_id']);
                $insertMatchStatement->bindParam(':w2', $resultupper[$i + 1]['tournamentmatch_id']);
                $insertMatchStatement->execute();
            }

            //select previous upperbracket matches
            $selectPreviousMatchessql = "SELECT tournamentmatch_id FROM tournamentmatch WHERE tournament_id = :tournament_id
                                                                 AND matchround = :matchround
                                                                 AND brackettype = :brackettype";
            $selectPreviousMatchesStatement = $pdo->prepare($selectPreviousMatchessql);
            $selectPreviousMatchesStatement->bindParam(':tournament_id', $tournament_id);
            $selectPreviousMatchesStatement->bindParam(':matchround', $matchround);
            $selectPreviousMatchesStatement->bindParam(':brackettype', $brackettype_U);
            $selectPreviousMatchesStatement->execute();
            $resultupper = $selectPreviousMatchesStatement->fetchAll(PDO::FETCH_ASSOC);

            //select previous lowerbracket matches
            $selectPreviousMatchessql = "SELECT tournamentmatch_id FROM tournamentmatch WHERE tournament_id = :tournament_id
                                                             AND matchround = :matchround
                                                             AND brackettype = :brackettype";
            $selectPreviousMatchesStatement = $pdo->prepare($selectPreviousMatchessql);
            $selectPreviousMatchesStatement->bindParam(':tournament_id', $tournament_id);
            $selectPreviousMatchesStatement->bindParam(':matchround', $matchround);
            $selectPreviousMatchesStatement->bindParam(':brackettype', $brackettype_L1);
            $selectPreviousMatchesStatement->execute();
            $resultlower = $selectPreviousMatchesStatement->fetchAll(PDO::FETCH_ASSOC);

            //insert L2 matches
            for ($i = 0; $i < $matchround; $i++) {
                $insertMatchSql = 'INSERT INTO tournamentmatch (tournament_id, matchround, brackettype, l1, w2)
                           VALUES (:tournament_id, :matchround, :brackettype, :l1, :w2)';
                $insertMatchStatement = $pdo->prepare($insertMatchSql);
                $insertMatchStatement->bindParam(':tournament_id', $tournament_id);
                $insertMatchStatement->bindParam(':matchround', $matchround);
                $insertMatchStatement->bindParam(':brackettype', $brackettype_L2);
                $insertMatchStatement->bindParam(':l1', $resultupper[$i]['tournamentmatch_id']);
                $insertMatchStatement->bindParam(':w2', $resultlower[$i]['tournamentmatch_id']);
                $insertMatchStatement->execute();
            }

//        $matchround = $matchround /2;
            //select previous lowerbracketmatches
            $selectPreviousMatchessql = "SELECT tournamentmatch_id FROM tournamentmatch WHERE tournament_id = :tournament_id
                                                             AND matchround = :matchround
                                                             AND brackettype = :brackettype";
            $selectPreviousMatchesStatement = $pdo->prepare($selectPreviousMatchessql);
            $selectPreviousMatchesStatement->bindParam(':tournament_id', $tournament_id);
            $selectPreviousMatchesStatement->bindParam(':matchround', $matchround);
            $selectPreviousMatchesStatement->bindParam(':brackettype', $brackettype_L2);
            $selectPreviousMatchesStatement->execute();
            $resultlower = $selectPreviousMatchesStatement->fetchAll(PDO::FETCH_ASSOC);
//            echo "<pre>", print_r($resultlower), "</pre>";

            $matchround = $matchround / 2;
            if ($matchround != '0.5') {
                //insert new round lowerbracketmatches
                for ($i = 0; $i < ($matchround * 2); $i += 2) {
                    $insertMatchSql = 'INSERT INTO tournamentmatch (tournament_id, matchround, brackettype, w1, w2)
                           VALUES (:tournament_id, :matchround, :brackettype, :w1, :w2)';
                    $insertMatchStatement = $pdo->prepare($insertMatchSql);
                    $insertMatchStatement->bindParam(':tournament_id', $tournament_id);
                    $insertMatchStatement->bindParam(':matchround', $matchround);
                    $insertMatchStatement->bindParam(':brackettype', $brackettype_L1);
                    $insertMatchStatement->bindParam(':w1', $resultlower[$i]['tournamentmatch_id']);
                    $insertMatchStatement->bindParam(':w2', $resultlower[$i + 1]['tournamentmatch_id']);
                    $insertMatchStatement->execute();
                }
            }
            $matchround = $matchround * 2;

        }


//finale
        $selectPreviousMatchessql = "SELECT tournamentmatch_id FROM tournamentmatch WHERE tournament_id = :tournament_id
                                                                 AND matchround = :matchround
                                                                 AND brackettype = :brackettype";
        $selectPreviousMatchesStatement = $pdo->prepare($selectPreviousMatchessql);
        $selectPreviousMatchesStatement->bindParam(':tournament_id', $tournament_id);
        $selectPreviousMatchesStatement->bindParam(':matchround', $matchround);
        $selectPreviousMatchesStatement->bindParam(':brackettype', $brackettype_U);
        $selectPreviousMatchesStatement->execute();
        $resultupper = $selectPreviousMatchesStatement->fetchAll(PDO::FETCH_ASSOC);


        $selectPreviousMatchessql = "SELECT tournamentmatch_id FROM tournamentmatch WHERE tournament_id = :tournament_id
                                                                 AND matchround = :matchround
                                                                 AND brackettype = :brackettype";
        $selectPreviousMatchesStatement = $pdo->prepare($selectPreviousMatchessql);
        $selectPreviousMatchesStatement->bindParam(':tournament_id', $tournament_id);
        $selectPreviousMatchesStatement->bindParam(':matchround', $matchround);
        $selectPreviousMatchesStatement->bindParam(':brackettype', $brackettype_L2);
        $selectPreviousMatchesStatement->execute();
        $resultlower = $selectPreviousMatchesStatement->fetchAll(PDO::FETCH_ASSOC);


        for ($i = 0; $i < $matchround; $i++) {
            $insertMatchSql = 'INSERT INTO tournamentmatch (tournament_id, matchround, brackettype, w1, w2)
                               VALUES (:tournament_id, :matchround, :brackettype, :w1, :w2)';
            $insertMatchStatement = $pdo->prepare($insertMatchSql);
            $insertMatchStatement->bindParam(':tournament_id', $tournament_id);
            $insertMatchStatement->bindParam(':matchround', $matchround);
            $insertMatchStatement->bindParam(':brackettype', $brackettype_F);
            $insertMatchStatement->bindParam(':w1', $resultupper[$i]['tournamentmatch_id']);
            $insertMatchStatement->bindParam(':w2', $resultlower[$i]['tournamentmatch_id']);
            $insertMatchStatement->execute();
        }
    }
}
$_SESSION['success'] = 'Double Elimination toernooi succesvol aangemaakt!';
Header('Location:../index.php?page=tournament_dashboard');
?>




<br>
<!--echo "<pre>", print_r($resultlower), "</pre>";-->