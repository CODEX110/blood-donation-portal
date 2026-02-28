<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// delete record
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM donation_records WHERE id=$id");
    header('Location: manage_donations.php');
    exit;
}

// fetch donation records with donor name
$result = $conn->query('SELECT r.*, d.name AS donor_name FROM donation_records r LEFT JOIN donors d ON r.donor_id=d.id ORDER BY donation_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Donations</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Manage Donations</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_donors.php">Manage Donors</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <div class="search-box">
        <input type="text" id="donation-search" placeholder="Search donations...">
    </div>
    <table class="admin-table" id="donation-table">
        <tr><th>ID</th><th>Date</th><th>Units</th><th>Hospital</th><th>Donor</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['donation_date']); ?></td>
                <td><?php echo (int)$row['units']; ?></td>
                <td><?php echo htmlspecialchars($row['hospital']); ?></td>
                <td><?php echo htmlspecialchars($row['donor_name']); ?></td>
                <td>
                    <a href="edit_donation.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="manage_donations.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this record?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<script>
    makeTableSearchable('#donation-table', '#donation-search');
    makeTableSortable('#donation-table');
</script>
</body>
</html>