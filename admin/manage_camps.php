<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// handle actions
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM camps WHERE id=$id");
    header('Location: manage_camps.php');
    exit;
}
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->query("UPDATE camps SET approved=1 WHERE id=$id");
    header('Location: manage_camps.php');
    exit;
}
if (isset($_GET['unapprove'])) {
    $id = (int)$_GET['unapprove'];
    $conn->query("UPDATE camps SET approved=0 WHERE id=$id");
    header('Location: manage_camps.php');
    exit;
}

$camps = $conn->query('SELECT * FROM camps ORDER BY event_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Camps</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Manage Camps</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_donors.php">Manage Donors</a>
        <a href="manage_requests.php">Manage Requests</a>
        <a href="manage_camps.php">Manage Camps</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <a href="edit_camp.php" class="btn">Add New Camp</a>
    <div class="search-box" style="margin:15px 0;">
        <input type="text" id="camp-search" placeholder="Search camps...">
    </div>
    <div class="search-box" style="margin:15px 0;">
        <input type="text" id="signup-search" placeholder="Search participants...">
    </div>
    <table class="admin-table" id="camp-table">
        <tr><th>ID</th><th>Title</th><th>Date</th><th>Location</th><th>Approved</th><th>Participants</th><th>Action</th></tr>
        <?php while ($row = $camps->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo $row['event_date']; ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo $row['approved'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $conn->query("SELECT COUNT(*) FROM camp_signups WHERE camp_id=".(int)$row['id'])->fetch_row()[0]; ?> <a href="manage_camp_signups.php?camp_id=<?php echo $row['id']; ?>">View</a></td>
                <td>
                    <?php if (!$row['approved']): ?>
                        <a href="manage_camps.php?approve=<?php echo $row['id']; ?>">Approve</a> |
                    <?php else: ?>
                        <a href="manage_camps.php?unapprove=<?php echo $row['id']; ?>">Unapprove</a> |
                    <?php endif; ?>
                    <a href="manage_camps.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<script>
    makeTableSearchable('#camp-table', '#');
    makeTableSortable('#camp-table');
</script>
</body>
</html>