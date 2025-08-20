<?php
include "db.php";
session_start();


if (isset($_SESSION['access_granted'])) {
    header("Location: hidden.php");
    exit;
}


$token = isset($_GET['token']) ? $_GET['token'] : '';
if ($token) {
    $result = $conn->query("SELECT * FROM passwords WHERE reset_token='$token' AND reset_expiry > NOW()");
    if ($result->num_rows == 0) {
        $error = "Invalid or expired reset token!";
        $token = '';
    }
}


if (isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'];
    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE passwords SET password='$hashed_password', reset_token=NULL, reset_expiry=NULL WHERE reset_token='$token'");
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
    <title>🔒 Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="password-container">
        <h2>Reset Password</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <?php if($token) { ?>
        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
        <?php } else { ?>
        <p>Invalid or expired reset link.</p>
        <?php } ?>
        <div class="navigation">
            <a href="index.php" class="nav-button">Back to My Goals</a>
            <a href="hidden.php" class="nav-button">Back to Login</a>
        </div>
    </div>
</body>
</html>