<?php
session_start();
include '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $blood_group = $_POST['blood_group'] ?? '';
    $profile_pic_path = null;

    // handle profile picture upload if present
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
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($phone)) {
        $error = "All fields are required!";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $created_at = date('Y-m-d H:i:s');

            $insert_sql = "INSERT INTO users (username, email, password, phone, blood_group, profile_pic, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sssssss", $username, $email, $hashed_password, $phone, $blood_group, $profile_pic_path, $created_at);

            if ($insert_stmt->execute()) {
                $success = "Signup successful! Redirecting to login...";
                header("Refresh: 2; url=login.php");
            } else {
                $error = "Error: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Signup - Blood Donation Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .signup-container {
            max-width: 500px;
            margin: 60px auto;
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
        .btn {
            width: 100%;
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
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <h1>Blood Donation Portal</h1>
    <nav class="main-nav">
        <a href="../index.php">Home</a>
        <a href="signup.php">User Signup</a>
        <a href="login.php">User Login</a>
        <a href="../admin/login.php">Admin</a>
    </nav>
</header>

<div class="container">
    <div class="signup-container">
        <h2>Create User Account</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" required>
            </div>

            <div class="form-group">
                <label>Blood Group</label>
                <select name="blood_group">
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            <div class="form-group">
                <label>Profile Picture (optional)</label>
                <input type="file" name="profile_pic" accept="image/*">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="pwd" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_pwd" required>
                <input type="checkbox" onclick="togglePassword()"> Show Password
            </div>

            <button class="btn" type="submit">Sign Up</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var pwd = document.getElementById("pwd");
    var confirm_pwd = document.getElementById("confirm_pwd");
    if (pwd.type === "password") {
        pwd.type = "text";
        confirm_pwd.type = "text";
    } else {
        pwd.type = "password";
        confirm_pwd.type = "password";
    }
}
</script>

</body>
</html>
