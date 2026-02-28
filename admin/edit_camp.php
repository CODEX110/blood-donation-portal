<?php
session_start();
include '../config.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$title=$location=$event_date=$details='';
approved = 1;
if ($id) {
    $stmt = $conn->prepare('SELECT * FROM camps WHERE id=?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row=$res->fetch_assoc()) {
        $title=$row['title'];
        $location=$row['location'];
        $event_date=$row['event_date'];
        $details=$row['details'];
        $approved = $row['approved'];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $location = $conn->real_escape_string($_POST['location']);
    $event_date = $_POST['event_date'];
    $details = $conn->real_escape_string($_POST['details']);
    $approved = isset($_POST['approved']) ? 1 : 0;
    if ($id) {
        $stmt = $conn->prepare('UPDATE camps SET title=?,location=?,event_date=?,details=?,approved=? WHERE id=?');
        $stmt->bind_param('ssssii',$title,$location,$event_date,$details,$approved,$id);
    } else {
        $stmt = $conn->prepare('INSERT INTO camps (title,location,event_date,details,approved) VALUES (?,?,?,?,?)');
        $stmt->bind_param('ssssi',$title,$location,$event_date,$details,$approved);
    }
    $stmt->execute();
    header('Location: manage_camps.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id ? 'Edit':'Add'; ?> Camp</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="admin-header">
    <h1><?php echo $id ? 'Edit':'Add'; ?> Camp</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_camps.php">Manage Camps</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-container">
    <form method="post">
        <div class="form-group"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required></div>
        <div class="form-group"><label>Location</label><input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" required></div>
        <div class="form-group"><label>Date</label><input type="date" name="event_date" value="<?php echo htmlspecialchars($event_date); ?>" required></div>
        <div class="form-group"><label>Details</label><textarea name="details"><?php echo htmlspecialchars($details); ?></textarea></div>
        <?php if ($id): ?>
        <div class="form-group"><label><input type="checkbox" name="approved" <?php echo $approved ? 'checked' : ''; ?>> Approved</label></div>
        <?php endif; ?>
        <button class="btn" type="submit">Save</button>
    </form>
</div>
</body>
</html>