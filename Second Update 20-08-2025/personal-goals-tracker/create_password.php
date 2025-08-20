<?php
include "db.php";
session_start();

if (isset($_SESSION['access_granted'])) {
    header("Location: hidden.php");
    exit;
}

$result = $conn->query("SELECT * FROM passwords");
if ($result->num_rows > 0) {
    header("Location: hidden.php");
    exit;
}

if (isset($_POST['create_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("INSERT INTO passwords (password) VALUES ('$hashed_password')");
        $_SESSION['access_granted'] = true;
        header("Location: hidden.php");
        exit;
    } else {
        $error = "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🔒 Create Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="password-container">
        <h2>Create Password</h2>
        <p>Set a password to protect your hidden goals.</p>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="create_password">Create Password</button>
        </form>
        <div class="navigation">
            <a href="index.php" class="nav-button">Back to My Goals</a>
        </div>
    </div>
</body>
</html>