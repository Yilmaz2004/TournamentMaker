<?php

$team_id = $_GET['team_id'] ?? 0;

// Fetch the current team details
$stmt = $pdo->prepare("SELECT * FROM teams WHERE team_id = :team_id");
$stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "Team not found.";
    exit();
}

// Fetch the list of stadiums
$stmt2 = $pdo->prepare("SELECT * FROM stadiums WHERE is_deleted = 0");
$stmt2->execute();
?>

<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>Edit Team</h2>
            <form action="php/teamsedit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="team_id" value="<?= $team_id ?>">

                <div class="form-group">
                    <label>Club Photo:</label>
                    <?php if (!empty($row['logo'])): ?>
                        <img src="data:image/jpeg;base64,<?= $row['logo'] ?>" alt="Club Logo" style="width:100px;">
                    <?php else: ?>
                        <i class="fa-solid fa-shield" style="font-size: 90px;"></i>
                    <?php endif; ?>
                    <input type="file" class="form-control mt-2" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Club Name:</label>
                    <input type="text" class="form-control" placeholder="Club Name" name="club" value="<?= ($row['club']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Stadium:</label>
                    <select class="form-control" name="stadium_id" required>
                        <?php while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $row2['stadium_id'] ?>" <?= $row2['stadium_id'] == $row['stadium_id'] ? 'selected' : '' ?>>
                                <?= ($row2['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <input type="reset">
                <button type="submit" class="btn btn-success">Update Team</button>
            </form>
        </div>
    </div>
</div>
</body>

