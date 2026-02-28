<?php 
include 'config.php';

/* Fetch Active Emergency Requests */
$emergency_sql = "SELECT * FROM requests 
                  WHERE status='Active' 
                  AND (expires_at IS NULL OR expires_at >= NOW())
                  ORDER BY created_at DESC 
                  LIMIT 5";

$emergency_result = $conn->query($emergency_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Donation Portal - Universal College of Arts and Science Mannarkkad</title>
    <meta name="description" content="Blood Donation Portal for students and staff of Universal College of Arts and Science, Mannarkkad.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php session_start(); ?>
<header>
    <h1>Universal College of Arts and Science, Mannarkkad</h1>
    <h2>Blood Donation Portal</h2>
    
    <nav class="main-nav"><br>
    
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="users/dashboard.php">Dashboard</a>
        <?php else: ?>
            <a href="users/login.php">User Login</a>
            <a href="users/signup.php">User Signup</a>
        <?php endif; ?>
        <a href="find_donor.php">Find Donor</a>
        <a href="camp.php">Blood Camp</a>
        <a href="emergency.php">Emergency</a>
        <a href="admin/login.php">Admin</a>
    </nav>

    <div class="dark-toggle">
        <label><input type="checkbox" id="darkmode"> Dark Mode</label>
    </div>
</header>

<div class="container">

    <!-- HERO SECTION -->
    <div class="hero">
        <h2 class="typewriter">Welcome to the Blood Donation Portal</h2>
        <p class="intro">
            Universal College of Arts and Science, Mannarkkad proudly supports a culture of giving. 
            Students and staff can register as donors or locate compatible donors quickly when needed.
        </p>
    </div>

    <!-- 🚨 EMERGENCY SECTION -->
    <div class="emergency-section">
        <h2 style="color:#c40000;">🚨 Active Emergency Blood Requests</h2>

        <?php if ($emergency_result && $emergency_result->num_rows > 0): ?>
            <?php while($row = $emergency_result->fetch_assoc()): ?>
                
                <div class="emergency-card">
                    <h3 style="color:red;">
                        <?php echo htmlspecialchars($row['blood_group']); ?> Blood Needed Urgently
                    </h3>
                    
                    <p><strong>Name:</strong> 
                        <?php echo htmlspecialchars($row['name']); ?>
                    </p>

                    <p><strong>Contact:</strong> 
                        <?php echo htmlspecialchars($row['contact']); ?>
                    </p>

                    <p><strong>Details:</strong> 
                        <?php echo htmlspecialchars($row['message']); ?>
                    </p>

                    <p style="font-size:13px;color:gray;">
                        Posted on: <?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?>
                    </p>

                    <a href="tel:<?php echo htmlspecialchars($row['contact']); ?>" class="call-btn">
                        📞 Call Now
                    </a>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:green;">No active emergency requests at the moment.</p>
        <?php endif; ?>
    </div>

    <!-- SLIDER -->
    <div class="slider" id="slider">
        <div class="slides">
            <div class="slide">
                <img src="images/1.png" alt="Donate Blood">
                <div class="caption">Donate Blood, Save Lives</div>
            </div>
            <div class="slide">
                <img src="images/2.png" alt="College Community">
                <div class="caption">NSS Blood Donation Drive</div>
            </div>
            <div class="slide">
                <img src="images/3.png" alt="Join the Cause">
                <div class="caption">Join the Cause Today</div>
            </div>
        </div>
        <button class="prev" id="prev">&#10094;</button>
        <button class="next" id="next">&#10095;</button>
        <div class="dots" id="dots"></div>
    </div>

    <h3>About Blood Donation</h3>
    <p>
        Blood donation is a noble, life-saving gesture. At our college everyone has the power 
        to make a difference — your single donation could help multiple patients in need.
    </p>
    <p>
        Donors enjoy health benefits such as refreshed blood cell production; recipients depend 
        on volunteers in crises, surgeries, and chronic care.
    </p>

    <h3>How It Works</h3>
    <ol>
        <li>Register using the "Register as Donor" link and fill in your details.</li>
        <li>Our team verifies and adds you to the donor directory.</li>
        <li>Recipients search by blood group; donor contact is provided securely.</li>
        <li>You decide when and where to donate – campus camps are held regularly.</li>
    </ol>

    <h3>Upcoming Drives</h3>
    <ul>
        <li>World Blood Donor Day camp – June 14, 2026 (College auditorium)</li>
        <li>Monthly mobile van visit – first Monday of every month</li>
        <li>Emergency alerts sent via email and notice board</li>
    </ul>

    <!-- TESTIMONIALS -->
    <div class="testimonial-slider" id="testimonial-slider">
        <div class="testi-item">"Amazing experience donating blood here!" - Student</div>
        <div class="testi-item">"Quick and easy registration." - Staff</div>
        <div class="testi-item">"The portal helped me find a donor fast." - Patient family</div>
    </div>

    <!-- FEATURES -->
    <div class="features">
        <div class="feature-item">
            <h4>Easy Registration</h4>
            <p>Sign up quickly with just a few details.</p>
        </div>
        <div class="feature-item">
            <h4>Find Donors</h4>
            <p>Search by blood group and contact directly.</p>
        </div>
        <div class="feature-item">
            <h4>Emergency Help</h4>
            <p>Post urgent requests and get notified fast.</p>
        </div>
        <div class="feature-item">
            <h4>24/7 Chatbot</h4>
            <p>Get instant answers about donation.</p>
        </div>
    </div>

</div>

<!-- Floating Donate Button -->
<a href="register.php" class="donate-btn">Donate Now</a>

<!-- Chat Widget -->
<div class="chatbox" id="chatbox">
    <div class="chatheader" id="chatheader">Chat</div>
    <div class="chatcontent" id="chatcontent"></div>
    <input type="text" id="chatinput" placeholder="Ask about blood donation..." />
</div>

<footer class="footer">
    &copy; <?php echo date('Y'); ?> Universal College of Arts and Science, Mannarkkad.
</footer>

<script src="js/scripts.js"></script>
</body>
</html>