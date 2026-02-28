<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: manage_donors.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $age = (int)$_POST['age'];
    $blood_group = $conn->real_escape_string($_POST['blood_group']);
    $role = $conn->real_escape_string($_POST['role']);
    $department = $conn->real_escape_string($_POST['department']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    $update_sql = "UPDATE donors SET name=?, gender=?, age=?, blood_group=?, role=?, department=?, email=?, phone=?, address=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ssissssssi', $name, $gender, $age, $blood_group, $role, $department, $email, $phone, $address, $id);
    if ($stmt->execute()) {
        $success = 'Donor updated successfully.';
    } else {
        $error = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}

$stmt = $conn->prepare('SELECT * FROM donors WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

if (!$donor) {
    header('Location: manage_donors.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Donor</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1>Edit Donor</h1>
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
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($donor['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Gender</label>
            <select name="gender">
                <option <?php echo $donor['gender']=='Male'?'selected':''; ?>>Male</option>
                <option <?php echo $donor['gender']=='Female'?'selected':''; ?>>Female</option>
                <option <?php echo $donor['gender']=='Other'?'selected':''; ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Age</label>
            <input type="number" name="age" min="18" max="65" value="<?php echo (int)$donor['age']; ?>" required>
        </div>
        <div class="form-group">
            <label>Blood Group</label>
            <input type="text" name="blood_group" value="<?php echo htmlspecialchars($donor['blood_group']); ?>">
        </div>
        <div class="form-group">
            <label>Role</label>
            <input type="text" name="role" value="<?php echo htmlspecialchars($donor['role']); ?>">
        </div>
        <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($donor['department']); ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($donor['email']); ?>">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($donor['phone']); ?>">
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address"><?php echo htmlspecialchars($donor['address']); ?></textarea>
        </div>
        <button class="btn" type="submit">Save Changes</button>
    </form>
</div>
</body>
</html>
