<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

/* DELETE REQUEST */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM requests WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_requests.php");
    exit;
}

$requestResult = $conn->query("SELECT * FROM requests ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Requests</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="admin-header">
    <h1>Manage Emergency Requests</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_donors.php">Manage Donors</a>
        <a href="manage_requests.php">Manage Requests</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="admin-container">
    <div class="search-box">
        <input type="text" id="request-search" placeholder="Search requests...">
    </div>

    <table class="admin-table" id="request-table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Blood Group</th>
            <th>Message</th>
            <th>Time</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $requestResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['contact']); ?></td>
                <td><?php echo htmlspecialchars($row['blood_group']); ?></td>
                <td><?php echo htmlspecialchars($row['message']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <!-- Call Button -->
                    <a href="tel:<?php echo htmlspecialchars($row['contact']); ?>" 
                       class="btn" 
                       style="background:green; padding:5px 10px; text-decoration:none; color:white;">
                       Call
                    </a>

                    <!-- Delete Button -->
                    <a href="?delete=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this request?');"
                       class="btn"
                       style="background:red; padding:5px 10px; text-decoration:none; color:white;">
                       Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
    makeTableSearchable('#request-table', '#request-search');
    makeTableSortable('#request-table');
</script>

</body>
</html>