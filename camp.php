<?php
include 'config.php';
$message='';
// signup handling
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['camp_id'])) {
    $camp_id = (int)$_POST['camp_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $sql = "INSERT INTO camp_signups (camp_id, name, email, phone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isss', $camp_id, $name, $email, $phone);
    if ($stmt->execute()) {
        $message = 'Registration successful. Thank you!';
    } else {
        $message = 'Error: '.$stmt->error;
    }
    $stmt->close();
}

// fetch active, approved camps
$camps = [];
$res = $conn->query('SELECT * FROM camps WHERE event_date >= CURDATE() AND approved=1 ORDER BY event_date ASC');
while ($row = $res->fetch_assoc()) {
    $camps[] = $row;
}
// statistics
$total_camps = $conn->query('SELECT COUNT(*) FROM camps')->fetch_row()[0];
$total_signups = $conn->query('SELECT COUNT(*) FROM camp_signups')->fetch_row()[0];
// handle user-proposed camp
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['propose'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $location = $conn->real_escape_string($_POST['location']);
    $date = $_POST['date'];
    $details = $conn->real_escape_string($_POST['details']);
    $stmt = $conn->prepare('INSERT INTO camps (title, location, event_date, details) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss',$title,$location,$date,$details);
    $stmt->execute();
    $stmt->close();
    $message = 'Your camp proposal has been submitted for approval.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Donation Camps</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Camps &amp; Events</h1>
    <nav class="main-nav">
        <a href="index.php">Home</a>
        <a href="register.php">Register</a>
        <a href="find_donor.php">Find Donor</a>
        <a href="camp.php">Blood Camp</a>
        <a href="emergency.php">Emergency</a>
    </nav>
    <?php if(isset($_SESSION['admin'])): ?>
        <div style="margin-top:10px;">
            <a href="admin/edit_camp.php" class="btn" style="background:#006600;">Conduct Camp</a>
        </div>
    <?php endif; ?>
</header>
<div class="container">
    <?php if($message): ?><p><?php echo $message; ?></p><?php endif; ?>
    <div class="camp-stats" style="margin-bottom:20px; text-align:center;">
        <span>Total camps recorded: <?php echo $total_camps; ?></span> &nbsp;|&nbsp; 
        <span>Total signups: <?php echo $total_signups; ?></span>
    </div>
    <div class="camp-description" id="camp-description">
        <p>Welcome to the Blood Camp section. Here you can either propose a new blood donation camp (conducted by NSS, Red Cross etc.) or register for any upcoming camp organised on campus. Click a button below to begin.</p>
    </div>
    <div class="camp-buttons" style="margin-bottom:20px; text-align:center;">
        <button class="btn" id="show-proposal">Conduct New Camp</button>
        <button class="btn" id="show-available">Available Camps</button>
    </div>
    <div id="proposal-container" style="display:none;">
    <section class="proposal-section">
        <h2>Conduct a New Camp</h2>
        <form method="post" class="proposal-form">
            <input type="hidden" name="propose" value="1">
            <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
            <div class="form-group"><label>Location</label><input type="text" name="location" required></div>
            <div class="form-group"><label>Date</label><input type="date" name="date" required></div>
            <div class="form-group"><label>Details</label><textarea name="details"></textarea></div>
            <button class="btn" type="submit">Submit Proposal</button>
        </form>
    </section>
    </div>
    <div id="available-container" style="display:none;">
    <section class="registration-section">
        <h2>Join a Camp</h2>
    <?php if ($camps): ?>
        <ul class="camp-list">
            <?php foreach($camps as $c): ?>
                <li class="camp-item">
                    <h3><?php echo htmlspecialchars($c['title']); ?> (<?php echo $c['event_date']; ?>)</h3>
                    <p>Location: <?php echo htmlspecialchars($c['location']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($c['details'])); ?></p>
                    <button class="btn register-camp" data-id="<?php echo $c['id']; ?>">Register Camp</button>
                    <div class="camp-form" id="form-<?php echo $c['id']; ?>" style="display:none;">
                        <form method="post">
                            <input type="hidden" name="camp_id" value="<?php echo $c['id']; ?>">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" required>
                            </div>
                            <button class="btn" type="submit">Sign Up</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No upcoming camps.</p>
    <?php endif; ?>
    </section>
    </div>
</div>
<script>
    document.querySelectorAll('.register-camp').forEach(btn => {
        btn.addEventListener('click', e => {
            const id = e.target.getAttribute('data-id');
            const form = document.getElementById('form-' + id);
            if (form.style.display === 'none') form.style.display = 'block';
            else form.style.display = 'none';
        });
    });
    // toggle sections
    const desc = document.getElementById('camp-description');
    document.getElementById('show-proposal').addEventListener('click', () => {
        desc.style.display = 'none';
        document.getElementById('proposal-container').style.display = 'block';
        document.getElementById('available-container').style.display = 'none';
    });
    document.getElementById('show-available').addEventListener('click', () => {
        desc.style.display = 'none';
        document.getElementById('proposal-container').style.display = 'none';
        document.getElementById('available-container').style.display = 'block';
    });
</script>
<footer class="footer">
    &copy; <?php echo date('Y'); ?> Universal College of Arts and Science.
</footer>
</body>
</html>