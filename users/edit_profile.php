<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current user data (including profile pic)
$user_sql = "SELECT username, email, phone, blood_group, willing_to_donate, donate_reason, last_donation_date, donation_count, donation_units, profile_pic FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $blood_group = $_POST['blood_group'];
    $willing = isset($_POST['willing']) && $_POST['willing'] === '1' ? 1 : 0;
    $reason = trim($_POST['donate_reason']);
    $last_date = $_POST['last_donation_date'] ?: null;
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_pic_path = $user['profile_pic'];

    // handle profile picture
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

    // Validation
    if (empty($phone)) {
        $error = "Phone number is required!";
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Update profile (stats are not editable by user)
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_sql = "UPDATE users SET phone = ?, blood_group = ?, willing_to_donate = ?, donate_reason = ?, last_donation_date = ?, profile_pic = ?, password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssissssi", $phone, $blood_group, $willing, $reason, $last_date, $profile_pic_path, $hashed_password, $user_id);
        } else {
            $update_sql = "UPDATE users SET phone = ?, blood_group = ?, willing_to_donate = ?, donate_reason = ?, last_donation_date = ?, profile_pic = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssisssi", $phone, $blood_group, $willing, $reason, $last_date, $profile_pic_path, $user_id);
        }

        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            // refresh local copy
            $user['phone'] = $phone;
            $user['blood_group'] = $blood_group;
            $user['willing_to_donate'] = $willing;
            $user['donate_reason'] = $reason;
            $user['last_donation_date'] = $last_date;
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Blood Donation Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .edit-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 5px rgba(220, 53, 69, 0.3);
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-style: italic;
        }
        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .btn {
            padding: 12px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .success {
            color: #155724;
            background: #d4edda;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .read-only-field {
            background: #e9ecef;
        }
        .read-only-field:focus {
            border-color: #ddd !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body>
<header>
    <h1>Blood Donation Portal</h1>
    <nav class="main-nav">
        <a href="../index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="edit-container">
        <h2>Edit Profile</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Profile Picture</label>
                <?php if (!empty($user['profile_pic'])): ?>
                    <div><img src="../<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile" style="max-width:100px; border-radius:50%;"></div>
                <?php endif; ?>
                <input type="file" name="profile_pic" accept="image/*">
                <small>Upload a new picture to replace existing one.</small>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" class="read-only-field" disabled>
                <small>Username cannot be changed</small>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="read-only-field" disabled>
                <small>Email cannot be changed</small>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label>Blood Group</label>
                <select name="blood_group">
                    <option value="">Select Blood Group</option>
                    <option value="A+" <?php echo $user['blood_group'] === 'A+' ? 'selected' : ''; ?>>A+</option>
                    <option value="A-" <?php echo $user['blood_group'] === 'A-' ? 'selected' : ''; ?>>A-</option>
                    <option value="B+" <?php echo $user['blood_group'] === 'B+' ? 'selected' : ''; ?>>B+</option>
                    <option value="B-" <?php echo $user['blood_group'] === 'B-' ? 'selected' : ''; ?>>B-</option>
                    <option value="O+" <?php echo $user['blood_group'] === 'O+' ? 'selected' : ''; ?>>O+</option>
                    <option value="O-" <?php echo $user['blood_group'] === 'O-' ? 'selected' : ''; ?>>O-</option>
                    <option value="AB+" <?php echo $user['blood_group'] === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                    <option value="AB-" <?php echo $user['blood_group'] === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                </select>
            </div>

            <div class="form-group">
                <label>Willing to donate blood?</label>
                <br>
                <label><input type="radio" name="willing" value="1" <?php echo $user['willing_to_donate'] ? 'checked' : ''; ?>> Yes</label>
                <label><input type="radio" name="willing" value="0" <?php echo !$user['willing_to_donate'] ? 'checked' : ''; ?>> No</label>
            </div>

            <div class="form-group">
                <label>Reason (if not willing)</label>
                <textarea name="donate_reason"><?php echo htmlspecialchars($user['donate_reason']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Last Donation Date</label>
                <input type="date" name="last_donation_date" value="<?php echo htmlspecialchars($user['last_donation_date']); ?>">
            </div>


            <hr>

            <h3>Change Password (Optional)</h3>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" id="new_pwd">
                <small>Leave blank if you don't want to change your password</small>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_pwd">
                <input type="checkbox" onclick="togglePassword()"> Show Password
            </div>

            <div class="btn-group">
                <button class="btn" type="submit">Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary" style="text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    var new_pwd = document.getElementById("new_pwd");
    var confirm_pwd = document.getElementById("confirm_pwd");
    if (new_pwd.type === "password") {
        new_pwd.type = "text";
        confirm_pwd.type = "text";
    } else {
        new_pwd.type = "password";
        confirm_pwd.type = "password";
    }
}
</script>

</body>
</html>
