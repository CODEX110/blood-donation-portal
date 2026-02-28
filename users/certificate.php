<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// fetch user info
$sql = "SELECT username, donation_count, donation_units, last_donation_date, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// if there is a last donation date, fetch hospital name
$user['last_hospital'] = '';
if (!empty($user['last_donation_date'])) {
    $hstmt = $conn->prepare('SELECT hospital FROM donation_records WHERE user_id = ? AND donation_date = ? ORDER BY created_at DESC LIMIT 1');
    $hstmt->bind_param('is', $user_id, $user['last_donation_date']);
    $hstmt->execute();
    $hres = $hstmt->get_result();
    if ($hrow = $hres->fetch_assoc()) {
        $user['last_hospital'] = $hrow['hospital'];
    }
    $hstmt->close();
}

// simple certificate page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Donation Certificate</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .certificate {
            max-width: 800px;
            margin: 60px auto;
            padding: 40px;
            border: 10px solid #dc3545;
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }
        .certificate h1 {
            margin-bottom: 0;
        }
        .certificate .details {
            margin-top: 20px;
            font-size: 18px;
        }
        .print-btn {
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="certificate">
    <h1>Blood Donation Certificate</h1>
    <p>This certifies that</p>
    <?php if (!empty($user['profile_pic'])): ?>
        <img src="../<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile" style="max-width:100px; border-radius:50%; margin:10px 0;">
    <?php endif; ?>
    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
    <p>has generously donated blood</p>
    <div class="details">
        <p><strong>Total Donations:</strong> <?php echo (int)$user['donation_count']; ?></p>
        <p><strong>Total Units:</strong> <?php echo (int)$user['donation_units']; ?></p>
        <p><strong>Last Donation:</strong> <?php echo $user['last_donation_date'] ? date('d-M-Y', strtotime($user['last_donation_date'])) : 'N/A'; ?></p>
        <?php if (!empty($user['last_hospital'])): ?>
        <p><strong>Hospital:</strong> <?php echo htmlspecialchars($user['last_hospital']); ?></p>
        <?php endif; ?>
    </div>
    <p>Thank you for your life-saving contribution!</p>
    <button class="btn print-btn" onclick="window.print();">Print / Save as PDF</button>
</div>
</body>
</html>
