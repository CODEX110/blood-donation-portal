<?php
include '../config.php';

$username = "ansil";
$password = password_hash("ansil123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);

if($stmt->execute()){
    echo "Admin created successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>