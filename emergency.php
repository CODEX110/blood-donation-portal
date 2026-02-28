<?php
include 'config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $blood_group = $conn->real_escape_string($_POST['blood_group']);
    $msg = $conn->real_escape_string($_POST['message']);
    $sql = "INSERT INTO requests (name, contact, blood_group, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $name, $contact, $blood_group, $msg);
    if ($stmt->execute()) {
        $message = 'Your emergency request has been submitted. We will notify donors shortly.';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emergency Blood Request</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Emergency Blood Request</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="register.php">Register</a>
        <a href="find_donor.php">Find Donor</a>
        <a href="emergency.php">Emergency</a>
    </nav>
</header>
<div class="container">
    <?php if ($message): ?><p><?php echo $message; ?></p><?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Your Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact" required>
        </div>
        <div class="form-group">
            <label>Required Blood Group</label>
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
            <label>Additional Details</label>
            <textarea name="message"></textarea>
        </div>
        <button class="btn" type="submit">Send Request</button>
    </form>
</div>
<footer class="footer">
    &copy; <?php echo date('Y'); ?> Universal College of Arts and Science.
</footer>
</body>
</html>