<?php
session_start();
include '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($user) && !empty($password)) {

        $sql = "SELECT * FROM admins WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {

                $_SESSION['admin'] = $admin['username'];
                header("Location: dashboard.php");
                exit();

            } else {
                $error = "Invalid username or password!";
            }

        } else {
            $error = "Invalid username or password!";
        }

        $stmt->close();
    } else {
        $error = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="admin-container" style="max-width:400px; margin:60px auto;">
    <h2>Admin Login</h2>

    <?php if (!empty($error)) : ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="pwd" required>
            <input type="checkbox" onclick="togglePassword()"> Show Password
        </div>

        <button class="btn" type="submit">Login</button>
    </form>
</div>

<script>
function togglePassword() {
    var x = document.getElementById("pwd");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
</script>

</body>
</html>