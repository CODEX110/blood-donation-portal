<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// handle deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM donors WHERE id=$id");
    header('Location: manage_donors.php');
    exit;
}

$result = $conn->query('SELECT * FROM donors ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Donors</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Manage Donors</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_donations.php">Manage Donations</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <div class="search-box">
        <input type="text" id="donor-search" placeholder="Search donors...">
    </div>
    <table class="admin-table" id="donor-table">
        <tr><th>ID</th><th>Name</th><th>Blood</th><th>Role</th><th>Department</th><th>Email</th><th>Phone</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['blood_group']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td>
                    <a href="edit_donor.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="record_donation.php?donor_id=<?php echo $row['id']; ?>">Donated</a> |
                    <a href="manage_donors.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this donor?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<script>
    makeTableSearchable('#donor-table', '#donor-search');
    makeTableSortable('#donor-table');
</script>
</body>
</html>