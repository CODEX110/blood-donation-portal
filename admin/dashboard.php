<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Admin Dashboard</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Home</a>
        <a href="manage_donors.php">Manage Donors</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_donors.php">Manage Donors</a>
        <a href="manage_donations.php">Manage Donations</a>
        <a href="manage_requests.php">Manage Requests</a>
        <a href="manage_camps.php">Manage Camps</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?>.</p>
</div>
</body>
</html>