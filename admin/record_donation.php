<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$donor_id = isset($_GET['donor_id']) ? (int)$_GET['donor_id'] : 0;
if ($donor_id <= 0) {
    header('Location: manage_donors.php');
    exit;
}

// fetch donor and associated user if exists
$stmt = $conn->prepare('SELECT d.*, u.id AS user_id FROM donors d LEFT JOIN users u ON d.email = u.email WHERE d.id=?');
$stmt->bind_param('i', $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

if (!$donor) {
    header('Location: manage_donors.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $units = (int)$_POST['units'];
    $hospital = $conn->real_escape_string($_POST['hospital']);
    $date = $_POST['donation_date'];

    // insert record
    $ins = $conn->prepare('INSERT INTO donation_records (user_id, donor_id, units, hospital, donation_date) VALUES (?, ?, ?, ?, ?)');
    $user_id = $donor['user_id'] ?: null;
    $ins->bind_param('iiiss', $user_id, $donor_id, $units, $hospital, $date);
    if ($ins->execute()) {
        // update user stats if user exists
        if ($user_id) {
            $upd = $conn->prepare('UPDATE users SET donation_count=donation_count+1, donation_units=donation_units+?, last_donation_date=? WHERE id=?');
            $upd->bind_param('isi', $units, $date, $user_id);
            $upd->execute();
            $upd->close();
        }
        $success = 'Donation recorded successfully.';
    } else {
        $error = 'Error: ' . $ins->error;
    }
    $ins->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Donation</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Record Donation</h1>
    <nav class="admin-nav">
        <a href="manage_donors.php">Back</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <?php if ($error): ?><p style="color:red"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green"><?php echo $success; ?></p><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Donor</label>
            <input type="text" value="<?php echo htmlspecialchars($donor['name']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Units Donated</label>
            <input type="number" name="units" min="1" required>
        </div>
        <div class="form-group">
            <label>Hospital</label>
            <input type="text" name="hospital" required>
        </div>
        <div class="form-group">
            <label>Date of Donation</label>
            <input type="date" name="donation_date" required>
        </div>
        <button class="btn" type="submit">Save</button>
    </form>
</div>
</body>
</html>
