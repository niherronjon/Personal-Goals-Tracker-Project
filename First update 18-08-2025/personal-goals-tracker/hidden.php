<?php
session_start();
include "db.php";

// Fetch the stored password
$result = $conn->query("SELECT password FROM passwords LIMIT 1");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hidden_password = $row['password'];
} else {
    // Redirect to create password if no password exists
    header("Location: create_password.php");
    exit;
}

// Check if password submitted
if (isset($_POST['enter_password'])) {
    if (password_verify($_POST['password'], $hidden_password)) {
        $_SESSION['access_granted'] = true;
    } else {
        $error = "Incorrect password!";
    }
}

// Handle forgot password
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $conn->query("UPDATE passwords SET reset_token='$token', reset_expiry='$expiry' WHERE id=1");
    $reset_link = "http://yourdomain.com/reset_password.php?token=$token"; // Replace with your domain
    $subject = "Password Reset Request";
    $message = "Click the following link to reset your password: $reset_link\nThis link will expire in 1 hour.";
    $headers = "From: noreply@yourdomain.com";
    mail($email, $subject, $message, $headers);
    $success = "A password reset link has been sent to your email.";
}

// Handle actions only if access is granted
if (isset($_SESSION['access_granted']) && $_SESSION['access_granted'] === true) {
    // Add Hidden Goal
    if (isset($_POST['add_hidden'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $type = $_POST['type'];
        $target_date = $_POST['target_date'];
        $conn->query("INSERT INTO hidden_goals (title, description, type, target_date) VALUES ('$title','$desc','$type','$target_date')");
        unset($_SESSION['access_granted']); // Force re-authentication
        header("Location: hidden.php");
        exit;
    }

    // Delete Hidden Goal
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $conn->query("DELETE FROM hidden_goals WHERE id=$id");
        unset($_SESSION['access_granted']); // Force re-authentication
        header("Location: hidden.php");
        exit;
    }

    // Complete Hidden Goal
    if (isset($_GET['complete'])) {
        $id = (int)$_GET['complete'];
        $conn->query("UPDATE hidden_goals SET status='completed' WHERE id=$id");
        unset($_SESSION['access_granted']); // Force re-authentication
        header("Location: hidden.php");
        exit;
    }

    // Undo Complete Hidden Goal
    if (isset($_GET['undo'])) {
        $id = (int)$_GET['undo'];
        $conn->query("UPDATE hidden_goals SET status='pending' WHERE id=$id");
        unset($_SESSION['access_granted']); // Force re-authentication
        header("Location: hidden.php");
        exit;
    }

    // Update Hidden Goal
    if (isset($_POST['update_hidden'])) {
        $id = (int)$_POST['id'];
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $type = $_POST['type'];
        $target_date = $_POST['target_date'];
        $conn->query("UPDATE hidden_goals SET title='$title', description='$desc', type='$type', target_date='$target_date' WHERE id=$id");
        unset($_SESSION['access_granted']); // Force re-authentication
        header("Location: hidden.php");
        exit;
    }
} else {
    // Unset session to ensure password is always required
    unset($_SESSION['access_granted']);
}

// If access not granted or session unset, show password form
if (!isset($_SESSION['access_granted']) || $_SESSION['access_granted'] !== true) { ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>🔒 Hidden Goals Access</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="password-container">
            <h2>Enter Password</h2>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
            <?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>
            <form method="post">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="enter_password">Enter</button>
            </form>
            <form method="post" class="forgot-password-form">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" name="forgot_password">Forgot Password?</button>
            </form>
            <div class="navigation">
                <a href="index.php" class="nav-button">Back to My Goals</a>
            </div>
        </div>
    </body>
    </html>
<?php exit(); } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🔒 Hidden Goals</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>🔒 Hidden Goals</header>
<div class="container">
    <div class="navigation">
        <a href="index.php" class="nav-button">My Goals</a>
        <a href="hidden.php" class="nav-button active">Hidden Goals</a>
    </div>

    <h2>Add Hidden Goal</h2>
    <form method="post">
        <input type="text" name="title" placeholder="Goal Title" required>
        <textarea name="description" placeholder="Goal Description"></textarea>
        <select name="type">
            <option value="short-term">Short-Term</option>
            <option value="long-term">Long-Term</option>
        </select>
        <input type="date" name="target_date" required>
        <button type="submit" name="add_hidden">Add Hidden Goal</button>
    </form>

    <h2>My Hidden Goals</h2>
    <?php
    $result = $conn->query("SELECT * FROM hidden_goals ORDER BY created_at DESC");
    $total = $result->num_rows;
    $done = $conn->query("SELECT * FROM hidden_goals WHERE status='completed'")->num_rows;
    $progress = $total > 0 ? round(($done/$total)*100) : 0;
    ?>
    <div class="progress-bar">
        <div class="progress-fill" style="width: <?= $progress ?>%"><?= $progress ?>%</div>
    </div>

    <table>
        <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Target Date</th>
            <th>Description</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= ucfirst($row['type']) ?></td>
            <td><?= date("M d, Y", strtotime($row['target_date'])) ?></td>
            <td class="description"><?= htmlspecialchars($row['description']) ?></td>
            <td class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
            <td><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
            <td class="actions">
                <a href="#" class="edit" onclick="openHiddenModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title'])) ?>', '<?= htmlspecialchars(addslashes($row['description'])) ?>', '<?= $row['type'] ?>', '<?= $row['target_date'] ?>')">✏ Edit</a>
                <?php if($row['status'] == 'completed') { ?>
                    <a href="?undo=<?= $row['id'] ?>" class="edit">↩ Undo</a>
                <?php } else { ?>
                    <a href="?complete=<?= $row['id'] ?>" class="complete">✔ Complete</a>
                <?php } ?>
                <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this hidden goal?')">🗑 Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

<!-- Modal for Editing Hidden Goals -->
<div id="editHiddenModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeHiddenModal()">&times;</span>
        <h2>Edit Hidden Goal</h2>
        <form method="post">
            <input type="hidden" name="id" id="edit_hidden_id">
            <input type="text" name="title" id="edit_hidden_title" required>
            <textarea name="description" id="edit_hidden_description"></textarea>
            <select name="type" id="edit_hidden_type">
                <option value="short-term">Short-Term</option>
                <option value="long-term">Long-Term</option>
            </select>
            <input type="date" name="target_date" id="edit_hidden_target_date" required>
            <button type="submit" name="update_hidden" class="edit">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openHiddenModal(id, title, desc, type, date) {
    document.getElementById('edit_hidden_id').value = id;
    document.getElementById('edit_hidden_title').value = title;
    document.getElementById('edit_hidden_description').value = desc;
    document.getElementById('edit_hidden_type').value = type;
    document.getElementById('edit_hidden_target_date').value = date;
    document.getElementById('editHiddenModal').style.display = 'block';
}
function closeHiddenModal() {
    document.getElementById('editHiddenModal').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target == document.getElementById('editHiddenModal')) {
        closeHiddenModal();
    }
}
</script>

</body>
</html>