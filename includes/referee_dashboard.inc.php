<?php
global $pdo;
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}
$query = "SELECT users.email AS email, users.firstname AS firstname, users.lastname AS lastname, users.infix AS infix, users.user_id AS user_id FROM users JOIN roles ON users.role_id = roles.role_id WHERE roles.role = 'referee' AND users.is_deleted = 0";

$stmt = $pdo->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="table-wrap">
                    <table class="table">
                        <thead class="thead-primary">
                        <tr>
                            <th>E-mail</th>
                            <th>Voornaam</th>
                            <th>Tussenvoegsel</th>
                            <th>Achternaam</th>
                            <th class="add-button">
                                <a href="index.php?page=add_referee" class="btn btn-success btn-sm"><i class="bi bi-plus"></i></a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['firstname']) ?></td>
                                    <td><?= htmlspecialchars($row['infix'] ?: '') ?></td>
                                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                                    <td>
                                        <a href="index.php?page=edit_referee&id=<?= $row['user_id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                        <a href="php/delete_referee.php?id=<?= $row['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No users found</td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
