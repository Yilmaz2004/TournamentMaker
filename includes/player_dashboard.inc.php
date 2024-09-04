<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}

unset($_SESSION['form_values']);

//select player info
$selectSql = 'SELECT players.player_id, players.firstname, players.infix, players.lastname, players.birthdate, players.image, legs.leg, positions.position, players.jerseynumber, teams.club
              FROM players
              LEFT JOIN legs ON players.leg_id = legs.leg_id
              LEFT JOIN positions ON players.position_id = positions.position_id
              LEFT JOIN teams ON players.team_id = teams.team_id
              WHERE players.is_deleted = 0';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->execute();

?>

<table id="overviewplayers">
    <tr>
        <th>Foto</th>
        <th>Voornaam</th>
        <th>Tussenvoegsel</th>
        <th>Achternaam</th>
        <th>Team</th>

        <th class="add-button">
            <a href="index.php?page=add_player" class="btn btn-success btn-sm"><i class="bi bi-plus"></i></a>
        </th>
    </tr>


    <?php
    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    { ?>
    <tr>
        <td id="td1"><img class="imageplayerdashboard" src="data:image/jpeg;base64,<?php echo base64_encode( $result['image'])?>" alt="Profielfoto"></td>
        <td><?php echo $result['firstname'] ?></td>
        <td><?php echo $result['infix'] ?></td>
        <td><?php echo $result['lastname']?></td>
        <td><?php echo $result['club'] ?></td>
        <?php
              echo'<td>
                    <button class="btn btn-primary onclick="location.href=\'index.php?page=playerdetails&player_id=' . $result['player_id'] . '\'"><i class="fa-solid fa-circle-info"></i></button>
                        <a href="index.php?page=edit_player&player_id=' . $result['player_id'] .  '" class="btn btn-primary "><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-danger " onclick="return confirmDelete(' . $result['player_id'] . ')"><i class="bi bi-trash"></i></button>
                   </td>';
        echo'</tr>';
        }
        ?>

</table>

<script>
    function confirmDelete(player_id)
    {
        var confirmResult = confirm('Weet je zeker dat je deze speler wilt verwijderen?')
        if(confirmResult)
        {
            location.href='php/delete_player.php?player_id=' +player_id
        }
            return confirmResult;
    }
</script>