<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: manage_users.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $conn->real_escape_string($_POST['phone']);
    $blood_group = $conn->real_escape_string($_POST['blood_group']);
    $willing = isset($_POST['willing']) ? 1 : 0;
    $reason = $conn->real_escape_string($_POST['donate_reason']);
    $last_date = $_POST['last_donation_date'] ?: null;
    $donation_count = (int)$_POST['donation_count'];
    $donation_units = (int)$_POST['donation_units'];
    $profile_pic_path = $user['profile_pic'];

    // handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['profile_pic']['type'], $allowedTypes)) {
                $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $newName = uniqid('pic_') . "." . $ext;
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $dest = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
                    $profile_pic_path = 'uploads/' . $newName;
                } else {
                    $error = "Failed to move uploaded file.";
                }
            } else {
                $error = "Only JPG, PNG and GIF images are allowed for profile picture.";
            }
        } else {
            $error = "Error uploading profile picture.";
        }
    }

    $update_sql = "UPDATE users SET phone=?, blood_group=?, willing_to_donate=?, donate_reason=?, last_donation_date=?, donation_count=?, donation_units=?, profile_pic=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ssissssis', $phone, $blood_group, $willing, $reason, $last_date, $donation_count, $donation_units, $profile_pic_path, $id);
    if ($stmt->execute()) {
        $success = 'User updated successfully.';
    } else {
        $error = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}

$stmt = $conn->prepare('SELECT * FROM users WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: manage_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Edit User</h1>
    <nav class="admin-nav">
        <a href="manage_users.php">Back</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <?php if ($error): ?><p style="color:red"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green"><?php echo $success; ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Profile Picture</label>
            <?php if (!empty($user['profile_pic'])): ?>
                <div><img src="../<?php echo htmlspecialchars($user['profile_pic']); ?>" style="max-width:80px;border-radius:50%;"></div>
            <?php endif; ?>
            <input type="file" name="profile_pic" accept="image/*">
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
        </div>
        <div class="form-group">
            <label>Blood Group</label>
            <input type="text" name="blood_group" value="<?php echo htmlspecialchars($user['blood_group']); ?>">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="willing" <?php echo $user['willing_to_donate'] ? 'checked' : ''; ?>> Willing to donate</label>
        </div>
        <div class="form-group">
            <label>Reason (if not willing)</label>
            <textarea name="donate_reason"><?php echo htmlspecialchars($user['donate_reason']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Last Donation Date</label>
            <input type="date" name="last_donation_date" value="<?php echo htmlspecialchars($user['last_donation_date']); ?>">
        </div>
        <div class="form-group">
            <label>Donation Count</label>
            <input type="number" name="donation_count" value="<?php echo (int)$user['donation_count']; ?>">
        </div>
        <div class="form-group">
            <label>Donation Units</label>
            <input type="number" name="donation_units" value="<?php echo (int)$user['donation_units']; ?>">
        </div>
        <button class="btn" type="submit">Update</button>
    </form>
</div>
</body>
</html>
