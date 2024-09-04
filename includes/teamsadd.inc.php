<?php
require 'connection.php';

// Fetching the list of stadiums for the dropdown
$stmt2 = $pdo->prepare("SELECT * FROM stadiums WHERE is_deleted = 0");
$stmt2->execute();
?>


<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>Add a Team</h2>
            <form action="php/teamsadd.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="clubLogo">Club logo:</label>
                    <input type="file" class="form-control" id="clubLogo" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="clubName">Club name:</label>
                    <input type="text" class="form-control" id="clubName" placeholder="Club name" name="club" required>
                </div>
                <div class="mb-3 mt-3">
                    <label for="stadium">Stadium:</label>
                    <select class="form-control" id="stadium" name="stadium_id" required>
                        <?php while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?= ($row2["stadium_id"]) ?>"><?= ($row2["name"]) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Add Team</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
