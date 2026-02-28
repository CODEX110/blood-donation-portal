<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: manage_donations.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $units = (int)$_POST['units'];
    $hospital = $conn->real_escape_string($_POST['hospital']);
    $date = $_POST['donation_date'];

    // compute difference for user stats if user exists
    $old_units = (int)$record['units'];
    $user_id = $record['user_id'];

    $upd = $conn->prepare('UPDATE donation_records SET units=?, hospital=?, donation_date=? WHERE id=?');
    $upd->bind_param('issi', $units, $hospital, $date, $id);
    if ($upd->execute()) {
        if ($user_id) {
            $delta = $units - $old_units;
            if ($delta !== 0) {
                $stats = $conn->prepare('UPDATE users SET donation_count = donation_count + 0, donation_units = donation_units + ? WHERE id = ?');
                // count doesn't change since it's still one record
                $stats->bind_param('ii', $delta, $user_id);
                $stats->execute();
                $stats->close();
            }
            // if date changed to later than last_donation_date, update last_donation_date/hospital
            if ($date && (!$record['donation_date'] || $date > $record['donation_date'])) {
                $upd2 = $conn->prepare('UPDATE users SET last_donation_date=?, donation_units=donation_units WHERE id=?');
                $upd2->bind_param('si', $date, $user_id);
                $upd2->execute();
                $upd2->close();
            }
        }
        $success = 'Donation record updated.';
    } else {
        $error = 'Error: ' . $upd->error;
    }
    $upd->close();
}

$stmt = $conn->prepare('SELECT * FROM donation_records WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record) {
    header('Location: manage_donations.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Donation Record</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Edit Donation Record</h1>
    <nav class="admin-nav">
        <a href="manage_donations.php">Back</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <?php if ($error): ?><p style="color:red"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green"><?php echo $success; ?></p><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Units</label>
            <input type="number" name="units" min="1" value="<?php echo (int)$record['units']; ?>" required>
        </div>
        <div class="form-group">
            <label>Hospital</label>
            <input type="text" name="hospital" value="<?php echo htmlspecialchars($record['hospital']); ?>" required>
        </div>
        <div class="form-group">
            <label>Donation Date</label>
            <input type="date" name="donation_date" value="<?php echo htmlspecialchars($record['donation_date']); ?>" required>
        </div>
        <button class="btn" type="submit">Save</button>
    </form>
</div>
</body>
</html>