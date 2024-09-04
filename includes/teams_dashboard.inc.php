<?php
// Retrieve teams data for display
$stmt = $pdo->query("SELECT t.*, s.name AS stadium_name FROM teams t 
                     LEFT JOIN stadiums s ON t.stadium_id = s.stadium_id 
                     WHERE t.is_deleted = 0 
                     ORDER BY t.team_id DESC");
?>

<div class="container">
    <div id="overviewstadiums">
        <div class="col-md-8">
            <?php
            if (isset($_SESSION['notification2'])) {
                echo '<p style="color:darkgreen;">' . $_SESSION['notification2'] . '</p>';
                unset($_SESSION['notification2']);
            }
            ?>
            <table class="table table-striped">
                <thead class="table-dark">
                <tr>
                    <th>Logo</th>
                    <th>Club</th>
                    <th>Stadium</th>
                    <th class="add-button">
                        <a href="index.php?page=teamsadd" class="btn btn-success btn-sm"><i class="bi bi-plus"></i></a>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['logo'])) { ?>
                                    <img src="data:image/jpeg;base64,<?= $row['logo'] ?>" alt="Club Logo" style="width:100px;">
                                <?php } else { ?>
                                    <i class="fa-solid fa-shield" style="font-size: 90px;"></i>
                                <?php } ?>
                            </td>
                            <td><?= htmlspecialchars($row['club']) ?></td>
                            <td><?= htmlspecialchars($row['stadium_name']) ?></td>
                            <td>
                                <button class="btn btn-primary" onclick="window.location.href='index.php?page=teamsinfo&team_id=<?= $row["team_id"] ?>'"><i class="fa-solid fa-circle-info"></i></button>
                                <button class="btn btn-warning" onclick="window.location.href='index.php?page=teamsedit&team_id=<?= $row["team_id"] ?>'"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-danger" onclick="if(confirm('Are you sure you want to delete this team?'))window.location.href='php/teamsdelete.php?team_id=<?= $row["team_id"] ?>'"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="4" class="text-center">No records found</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
