<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header('Location: manage_users.php');
    exit;
}

// fetch users
$result = $conn->query('SELECT id, username, email, phone, blood_group, willing_to_donate, donation_count, donation_units, profile_pic FROM users ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Manage Users</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_donors.php">Manage Donors</a>
        <a href="manage_donations.php">Manage Donations</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <div class="search-box">
        <input type="text" id="user-search" placeholder="Search users...">
    </div>
    <table class="admin-table" id="user-table">
        <tr><th>ID</th><th>Photo</th><th>Username</th><th>Email</th><th>Phone</th><th>Blood</th><th>Willing</th><th>Donations</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo !empty($row['profile_pic']) ? '<img src="../'.htmlspecialchars($row['profile_pic']).'" style="max-width:40px;border-radius:50%;">' : ''; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['blood_group']); ?></td>
                <td><?php echo $row['willing_to_donate'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo (int)$row['donation_count']; ?>/<?php echo (int)$row['donation_units']; ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="manage_users.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this user?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<script>
    makeTableSearchable('#user-table', '#user-search');
    makeTableSortable('#user-table');
</script>
</body>
</html>
