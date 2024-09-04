<?php
include "../connection.php";

try {
    // Retrieve form data
    $clubName = $_POST['club'];
    $stadiumId = $_POST['stadium_id'];

    // Process image upload if a file is provided
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name'] != '') {
        $image = base64_encode(file_get_contents($_FILES['image']['tmp_name']));
    } else {
        // Set image to null if no file is uploaded
        $image = null;
    }

    // Insert data into teams table
    $sql = "INSERT INTO teams (logo, club, stadium_id, is_deleted) VALUES (:logo, :club, :stadium_id, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':logo', $image, PDO::PARAM_LOB);
    $stmt->bindParam(':club', $clubName);
    $stmt->bindParam(':stadium_id', $stadiumId);
    $stmt->execute(); // Execute the statement

    // Redirect after successful insertion
    header('Location: ../index.php?page=teams_dashboard');
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>