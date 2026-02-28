<?php
session_start();
include 'config.php';
$blood = '';
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood = $conn->real_escape_string($_POST['blood_group']);
    // only show donors whose user profile indicates they are willing
    $sql = "SELECT d.* FROM donors d
            JOIN users u ON d.email = u.email
            WHERE d.blood_group = ? AND u.willing_to_donate = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $blood);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Donor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Find a Blood Donor</h1>
    <nav>
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="users/dashboard.php">Dashboard</a>
        <?php else: ?>
            <a href="users/signup.php">User Signup</a>
            <a href="users/login.php">User Login</a>
        <?php endif; ?>
        <a href="find_donor.php">Find Donor</a>
        <a href="camp.php">Blood Camp</a>
        <a href="emergency.php">Emergency</a>
    </nav>
</header>
<div class="container">
    <form method="post">
        <div class="form-group">
            <label>Select Blood Group</label>
            <select name="blood_group">
                <option value="">--choose--</option>
                <option>A+</option>
                <option>A-</option>
                <option>B+</option>
                <option>B-</option>
                <option>O+</option>
                <option>O-</option>
                <option>AB+</option>
                <option>AB-</option>
            </select>
        </div>
        <button class="btn" type="submit">Search</button>
    </form>
    <?php if ($results): ?>
    <h2>Matching Donors</h2>
    <table class="table">
        <tr><th>Name</th><th>Blood</th><th>Role</th><th>Dept</th><th>Email</th><th>Phone</th><th>Contact</th></tr>
        <?php foreach ($results as $don): ?>
            <tr>
                <td><?php echo htmlspecialchars($don['name']); ?></td>
                <td><?php echo htmlspecialchars($don['blood_group']); ?></td>
                <td><?php echo htmlspecialchars($don['role']); ?></td>
                <td><?php echo htmlspecialchars($don['department']); ?></td>
                <td><?php echo htmlspecialchars($don['email']); ?></td>
                <td><?php echo htmlspecialchars($don['phone']); ?></td>
                <td>
                    <a class="call-icon" href="tel:<?php echo htmlspecialchars($don['phone']); ?>">Call</a>
                    |
                    <a class="whatsapp-icon" href="https://wa.me/<?php echo preg_replace('/\D/','', $don['phone']); ?>" target="_blank">WhatsApp</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>No donors found for selected blood group.</p>
    <?php endif; ?>
</div>
<footer class="footer">
    &copy; <?php echo date('Y'); ?> Universal College of Arts and Science.
</footer>
<script src="js/scripts.js"></script>
</body>
</html>
