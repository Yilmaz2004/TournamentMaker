<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?=login");
    exit;
}
include "connection.php"; // Ensure this file contains the correct path

$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="container-add-referee mt-5">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid-password'): ?>
        <div class="alert alert-danger alert-dismissible">
            <strong>Ongeldig Wachtwoord. Probeer het opnieuw.</strong>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid-email-write'): ?>
        <div class="alert alert-danger alert-dismissible">
            <strong>Ongeldig E-mail.</strong>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid-email'): ?>
        <div class="alert alert-danger alert-dismissible">
            <strong>E-mail bestaat al.</strong>
        </div>
    <?php endif; ?>
    <form action="php/edit_referee.php" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="mb-3">
            <label for="firstname" class="form-label">Voornaam</label>
            <input type="text" class="form-control" id="firstname" name="firstname" required value="<?php echo htmlspecialchars($user['firstname']); ?>">
        </div>
        <div class="mb-3">
            <label for="infix" class="form-label">Tussenvoegsel</label>
            <input type="text" class="form-control" id="infix" name="infix" value="<?php echo htmlspecialchars($user['infix']); ?>">
        </div>
        <div class="mb-3">
            <label for="lastname" class="form-label">Achternaam</label>
            <input type="text" class="form-control" id="lastname" name="lastname" required value="<?php echo htmlspecialchars($user['lastname']); ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Nieuw Wachtwoord</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Wachtwoord Herhalen</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
        </div>
        <div class="form-button">
            <button name="submit" type="submit" class="btn btn-success">Update</button>
            <button type="button" class="btn btn-primary"><a class="back-button-ref" href="index.php?page=referee_dashboard">Back</a></button>
        </div>
    </form>
</div>
<script>
    window.onload = function() {
        var url = new URL(window.location.href);
        var searchParams = url.searchParams;

        if (searchParams.has('error')) {
            var errorValue = searchParams.get('error');
            if (errorValue === 'invalid-password' || errorValue === 'invalid-email-write' || errorValue === 'invalid-email') {
                searchParams.delete('error');
                window.history.replaceState({}, document.title, url.pathname + '?' + searchParams.toString());
            }
        }
    };

</script>