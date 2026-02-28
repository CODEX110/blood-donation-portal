<?php
session_start();
include 'config.php';
$message = '';

// ensure only logged-in users can register donors
if (!isset($_SESSION['user_id'])) {
    // show message and stop processing form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $message = 'You must be <a href="users/login.php">logged in</a> to register as a donor. Please login or <a href="users/signup.php">sign up</a>.';
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $conn->real_escape_string($_POST['name']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $age = (int)$_POST['age'];
        $blood_group = $conn->real_escape_string($_POST['blood_group']);
        $role = $conn->real_escape_string($_POST['role']);
        $department = $conn->real_escape_string($_POST['department']);
        // use email from session to tie registration to user
        $email = $conn->real_escape_string($_SESSION['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);

        $sql = "INSERT INTO donors (name, gender, age, blood_group, role, department, email, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssissssss', $name, $gender, $age, $blood_group, $role, $department, $email, $phone, $address);
        if ($stmt->execute()) {
            // stats will be updated later when admin records donation
            $message = 'Registration successful. Thank you for volunteering as a donor!';
        } else {
            $message = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register as Donor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Register as Blood Donor</h1>
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
    <?php if ($message): ?><p><?php echo $message; ?></p><?php endif; ?>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- prompt login/signup when not authenticated -->
        <p>You must first <a href="users/login.php">log in</a> or <a href="users/signup.php">sign up</a> to register as a donor.</p>
    <?php else: ?>
        <form name="regForm" method="post" onsubmit="return validateRegistration();">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" min="18" max="65" required>
            </div>
            <div class="form-group">
                <label>Blood Group</label>
                <select name="blood_group">
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
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option>Student</option>
                    <option>Staff</option>
                </select>
            </div>
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address"></textarea>
            </div>
            <button type="submit" class="btn">Submit</button>
        </form>
    <?php endif; ?>
</div>
<footer class="footer">
    &copy; <?php echo date('Y'); ?> Universal College of Arts and Science.
</footer>
<script src="js/scripts.js"></script>
</body>
</html>