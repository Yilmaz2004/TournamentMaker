<?php
if(isset($_SESSION['error'])){ ?>
    <div class="alert">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <strong></strong><?php echo $_SESSION['error'] ?>
    </div>
    <?php
    unset($_SESSION['error']);
}
?>

<?php
if(isset($_SESSION['success'])){ ?>
    <div class="alert success">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <strong></strong><?php echo $_SESSION['success'] ?>
    </div>
    <?php
    unset($_SESSION['success']);
}
?>