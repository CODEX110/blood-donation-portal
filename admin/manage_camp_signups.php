<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$camp_id = isset($_GET['camp_id']) ? (int)$_GET['camp_id'] : 0;
$where = '';
if ($camp_id) {
    $where = "WHERE camp_id=$camp_id";
}

// optional join to get camp title
$sql = "SELECT s.*, c.title FROM camp_signups s LEFT JOIN camps c ON s.camp_id=c.id $where ORDER BY registered_at DESC";
$signups = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camp Signups</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Camp Participants</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_donors.php">Manage Donors</a>
        <a href="manage_requests.php">Manage Requests</a>
        <a href="manage_camps.php">Manage Camps</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <?php if ($camp_id): ?>
        <p>Showing signups for camp ID <?php echo $camp_id; ?> (<a href="manage_camps.php">back to camps</a>)</p>
    <?php endif; ?>
    <table class="admin-table" id="signup-table">
        <tr><th>ID</th><th>Camp</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Contact</th></tr>
        <?php while ($row = $signups->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo $row['registered_at']; ?></td>
                <td>
                    <?php if ($row['phone']): ?>
                        <a class="call-icon" href="tel:<?php echo $row['phone']; ?>">Call</a>
                        | <a class="whatsapp-icon" href="https://wa.me/<?php echo preg_replace('/[^0-9]/','',$row['phone']); ?>" target="_blank">WhatsApp</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<script>
    makeTableSearchable('#signup-table', '#signup-search');
    makeTableSortable('#signup-table');
</script>
</body>
</html>