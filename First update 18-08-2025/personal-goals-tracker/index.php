<?php include "db.php"; ?>

<?php
// Add Goal
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $type = $_POST['type'];
    $target_date = $_POST['target_date'];
    $conn->query("INSERT INTO goals (title, description, type, target_date) VALUES ('$title','$desc','$type','$target_date')");
    header("Location: index.php");
    exit;
}

// Delete Goal
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM goals WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Complete Goal
if (isset($_GET['complete'])) {
    $id = (int)$_GET['complete'];
    $conn->query("UPDATE goals SET status='completed' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Undo Complete Goal
if (isset($_GET['undo'])) {
    $id = (int)$_GET['undo'];
    $conn->query("UPDATE goals SET status='pending' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Update Goal
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $type = $_POST['type'];
    $target_date = $_POST['target_date'];
    $conn->query("UPDATE goals SET title='$title', description='$desc', type='$type', target_date='$target_date' WHERE id=$id");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🎯 Personal Goals Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>🎯 Personal Goals Tracker</header>
<div class="container">
    <div class="navigation">
        <a href="index.php" class="nav-button active">My Goals</a>
        <a href="hidden.php" class="nav-button">Hidden Goals</a>
    </div>

    <h2>Add New Goal</h2>
    <form method="post">
        <input type="text" name="title" placeholder="Goal Title" required>
        <textarea name="description" placeholder="Goal Description"></textarea>
        <select name="type">
            <option value="short-term">Short-Term</option>
            <option value="long-term">Long-Term</option>
        </select>
        <input type="date" name="target_date" required>
        <button type="submit" name="add">Add Goal</button>
    </form>

    <h2>My Goals</h2>
    <?php
    $result = $conn->query("SELECT * FROM goals ORDER BY created_at DESC");
    $total = $result->num_rows;
    $done = $conn->query("SELECT * FROM goals WHERE status='completed'")->num_rows;
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
                <a href="#" class="edit" onclick="openModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title'])) ?>', '<?= htmlspecialchars(addslashes($row['description'])) ?>', '<?= $row['type'] ?>', '<?= $row['target_date'] ?>')">✏ Edit</a>
                <?php if($row['status'] == 'completed') { ?>
                    <a href="?undo=<?= $row['id'] ?>" class="edit">↩ Undo</a>
                <?php } else { ?>
                    <a href="?complete=<?= $row['id'] ?>" class="complete">✔ Complete</a>
                <?php } ?>
                <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this goal?')">🗑 Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

<!-- Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Goal</h2>
        <form method="post">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="title" id="edit_title" required>
            <textarea name="description" id="edit_description"></textarea>
            <select name="type" id="edit_type">
                <option value="short-term">Short-Term</option>
                <option value="long-term">Long-Term</option>
            </select>
            <input type="date" name="target_date" id="edit_target_date" required>
            <button type="submit" name="update" class="edit">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openModal(id, title, desc, type, date) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_description').value = desc;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_target_date').value = date;
    document.getElementById('editModal').style.display = 'block';
}
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        closeModal();
    }
}
</script>

</body>
</html>