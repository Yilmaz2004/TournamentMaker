<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}

unset($_SESSION['form_values']);

$is_deleted = '1';

//select stadium info
$selectSql = 'SELECT * FROM stadiums WHERE is_deleted != :is_deleted';
$selectStatement = $pdo->prepare($selectSql);
$selectStatement->bindParam(':is_deleted',$is_deleted);
$selectStatement->execute();

?>


<table id="overviewstadiums">
    <tr>
        <th>Afbeelding</th>
        <th>Naam</th>
        <th>Stoelen</th>
        <th>Adres</th>
        <th class="add-button">
            <a href="index.php?page=add_stadium" class="btn btn-success btn-sm"><i class="bi bi-plus"></i></a>
        </th>
    </tr>

    <?php

    while($result = $selectStatement->fetch(PDO::FETCH_ASSOC))
    {
        echo '<tr>';
        echo '<td id="td1"><img class="imagestadium" src="data:image/pjeg;base64,' . base64_encode($result['image']) . '" alt="Afbeelding"></td>';
        echo '<td>' . $result['name'] . '</td>';
        echo '<td>' . $result['seats'] . '</td>';
        echo '<td>' . $result['address'] . '</td>';
        echo '<td>
                    <a href="index.php?page=edit_stadium&stadium_id=' . $result['stadium_id'] . '" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-danger btn-sm" onclick="return confirmDelete(' . $result['stadium_id'] . ')"><i class="bi bi-trash"></i></button>
              </td>';
        echo '</tr>';
    }
    ?>

</table>


<script>
    function confirmDelete(stadium_id)
    {
        var confirmResult = confirm('Weet je zeker dat je dit stadion wilt verwijderen?')
        if (confirmResult)
        {
            location.href='php/delete_stadium.php?stadium_id=' + stadium_id;
        }
        return confirmResult;
    }

</script>