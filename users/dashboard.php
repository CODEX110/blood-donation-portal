<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile
$user_sql = "SELECT id, username, email, phone, blood_group, willing_to_donate, donate_reason, last_donation_date, donation_count, donation_units, profile_pic, created_at FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_profile = $user_result->fetch_assoc();
$user_stmt->close();

// fetch last donation hospital if available
$last_hosp = '';
if ($user_profile && $user_profile['last_donation_date']) {
    $hstmt = $conn->prepare('SELECT hospital FROM donation_records WHERE user_id = ? AND donation_date = ? ORDER BY created_at DESC LIMIT 1');
    $hstmt->bind_param('is', $user_id, $user_profile['last_donation_date']);
    $hstmt->execute();
    $hres = $hstmt->get_result();
    if ($hrow = $hres->fetch_assoc()) {
        $last_hosp = $hrow['hospital'];
    }
    $hstmt->close();
}

// Fetch user's donation history from donation_records (include donor details)
$donation_sql = "SELECT r.id, r.units, r.hospital, r.donation_date, d.name
                 FROM donation_records r
                 LEFT JOIN donors d ON r.donor_id = d.id
                 WHERE r.user_id = ?
                 ORDER BY r.donation_date DESC
                 LIMIT 10";
$donation_stmt = $conn->prepare($donation_sql);
$donation_stmt->bind_param("i", $user_id);
$donation_stmt->execute();
$donation_result = $donation_stmt->get_result();
$donation_stmt->close();

// Fetch general blood requests (active ones)
$request_sql = "SELECT id, name, blood_group, contact, message, created_at FROM requests ORDER BY created_at DESC LIMIT 5";
$request_stmt = $conn->prepare($request_sql);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
$request_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Blood Donation Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 30px;
            margin: 30px 0;
        }
        .sidebar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
        }
        .sidebar h3 {
            margin-top: 0;
            color: #dc3545;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 10px 0;
        }
        .sidebar ul li a {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
        }
        .sidebar ul li a:hover {
            text-decoration: underline;
        }
        .content {
            background: white;
        }
        .section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .section h2 {
            color: #dc3545;
            margin-top: 0;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #dc3545;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 16px;
            color: #333;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        table thead {
            background: #dc3545;
            color: white;
        }
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table tbody tr:hover {
            background: #f8f9fa;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status.active {
            background: #d4edda;
            color: #155724;
        }
        .status.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .profile-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<header>
    <h1>Blood Donation Portal</h1>
    <nav class="main-nav">
        <a href="../index.php">Home</a>
        <a href="../find_donor.php">Find Donor</a>
        <a href="../emergency.php">Emergency</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Welcome!</h3>
            <?php if (!empty($user_profile['profile_pic'])): ?>
                <div><img src="../<?php echo htmlspecialchars($user_profile['profile_pic']); ?>" alt="Profile" style="max-width:80px; border-radius:50%;"></div>
            <?php endif; ?>
            <p><strong><?php echo htmlspecialchars($user_profile['username']); ?></strong></p>
            <hr>
            <h4>Quick Links</h4>
            <ul>
                <li><a href="dashboard.php">My Profile</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="certificate.php" target="_blank">Download Certificate</a></li>
                <li><a href="../find_donor.php">Find Donors</a></li>
                <li><a href="../emergency.php">Emergency Requests</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="content">
            <!-- Profile Section -->
            <div class="section">
                <h2>User Profile</h2>
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['phone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Blood Group</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['blood_group'] ?: 'Not specified'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Last Donation</div>
                        <div class="info-value"><?php echo $user_profile['last_donation_date'] ? date('d-M-Y', strtotime($user_profile['last_donation_date'])) : 'Never'; ?></div>
                    </div>
                    <?php if ($last_hosp): ?>
                    <div class="info-item">
                        <div class="info-label">Hospital</div>
                        <div class="info-value"><?php echo htmlspecialchars($last_hosp); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ((int)$user_profile['donation_count'] > 0): ?>
                    <div class="info-item">
                        <div class="info-label">Donation Count</div>
                        <div class="info-value"><?php echo (int)$user_profile['donation_count']; ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ((int)$user_profile['donation_units'] > 0): ?>
                    <div class="info-item">
                        <div class="info-label">Total Units Donated</div>
                        <div class="info-value"><?php echo (int)$user_profile['donation_units']; ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <div class="info-label">Willing to Donate</div>
                        <div class="info-value"><?php echo $user_profile['willing_to_donate'] ? 'Yes' : 'No'; ?></div>
                    </div>
                    <?php if (!$user_profile['willing_to_donate'] && !empty($user_profile['donate_reason'])): ?>
                    <div class="info-item">
                        <div class="info-label">Reason</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['donate_reason']); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?php echo date('d-M-Y', strtotime($user_profile['created_at'])); ?></div>
                    </div>
                </div>
            </div>

            <!-- Donation History -->
            <div class="section">
                <h2>Donation History</h2>
                <?php if ($donation_result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Units</th>
                                <th>Hospital</th>
                                <th>Donor Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($donation = $donation_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d-M-Y', strtotime($donation['donation_date'])); ?></td>
                                    <td><?php echo (int)$donation['units']; ?></td>
                                    <td><?php echo htmlspecialchars($donation['hospital']); ?></td>
                                    <td><?php echo htmlspecialchars($donation['name']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">No donation history found. <a href="../register.php">Register as a donor</a></div>
                <?php endif; ?>
            </div>

            <!-- Blood Requests -->
            <div class="section">
                <h2>Latest Blood Requests</h2>
                <?php if ($request_result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Blood Group</th>
                                <th>Contact</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($request = $request_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d-M-Y', strtotime($request['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($request['name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($request['contact']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($request['message'], 0, 50)); ?>...</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">No blood requests at this time.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
