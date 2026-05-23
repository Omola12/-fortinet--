<?php
session_start();
require_once 'functions.php';
initDataFile();

// Handle complaint submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $complaint_text = trim($_POST['complaint']);
    
    if (empty($name) || empty($email) || empty($complaint_text)) {
        $errorMessage = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address';
    } else {
        $complaint = [
            'id' => generateUniqueId(),
            'name' => $name,
            'email' => $email,
            'complaint' => $complaint_text,
            'status' => 'pending',
            'admin_reply' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'replied_at' => null
        ];
        
        if (saveComplaint($complaint)) {
            $successMessage = 'Your complaint has been submitted successfully! Your ID is: ' . $complaint['id'];
            $_POST = []; // Clear form
        } else {
            $errorMessage = 'Failed to submit complaint. Please try again.';
        }
    }
}

// Get user complaints if email is provided
$userEmail = isset($_GET['email']) ? $_GET['email'] : (isset($_POST['check_email']) ? $_POST['check_email'] : '');
$userComplaints = [];
if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    $userComplaints = getUserComplaints($userEmail);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 File a Complaint</h1>
            <p>Your voice matters. Submit your complaint and track its status.</p>
        </div>
        
        <?php if ($successMessage): ?>
        <div class="alert alert-success" style="display:block; background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px;">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
        <div class="alert alert-error" style="display:block; background:#f8d7da; color:#721c24; padding:15px; border-radius:10px; margin-bottom:20px;">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>
        
        <div class="complaint-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Your Complaint *</label>
                    <textarea name="complaint" required><?php echo isset($_POST['complaint']) ? htmlspecialchars($_POST['complaint']) : ''; ?></textarea>
                </div>
                <button type="submit" name="submit_complaint" class="btn-submit">Submit Complaint</button>
            </form>
        </div>
        
        <div class="complaints-list">
            <h2>🔍 Track Your Complaints</h2>
            <form method="GET" action="" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px;">
                    <input type="email" name="email" placeholder="Enter your email to view complaints" 
                           style="flex:1; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;"
                           value="<?php echo htmlspecialchars($userEmail); ?>">
                    <button type="submit" style="background:#667eea; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">View</button>
                </div>
            </form>
            
            <?php if ($userEmail && !filter_var($userEmail, FILTER_VALIDATE_EMAIL)): ?>
                <div class="alert alert-error" style="display:block;">Please enter a valid email address</div>
            <?php elseif ($userEmail && empty($userComplaints)): ?>
                <div class="no-complaints">No complaints found for this email address.</div>
            <?php elseif (!empty($userComplaints)): ?>
                <?php foreach ($userComplaints as $complaint): ?>
                <div class="complaint-card">
                    <div class="complaint-header">
                        <span class="complaint-id">ID: <?php echo htmlspecialchars($complaint['id']); ?></span>
                        <span class="complaint-date"><?php echo htmlspecialchars($complaint['created_at']); ?></span>
                    </div>
                    <div class="complaint-message">
                        <strong>Complaint:</strong><br>
                        <?php echo nl2br(htmlspecialchars($complaint['complaint'])); ?>
                    </div>
                    <?php if ($complaint['admin_reply']): ?>
                    <div class="reply-section">
                        <div class="reply-label">✅ Admin Response:</div>
                        <div class="reply-text"><?php echo nl2br(htmlspecialchars($complaint['admin_reply'])); ?></div>
                        <small style="color:#666;">Replied on: <?php echo htmlspecialchars($complaint['replied_at']); ?></small>
                    </div>
                    <?php else: ?>
                    <div class="reply-section" style="background:#fff3e0;">
                        <div class="reply-label">⏳ Status: Pending Review</div>
                        <div class="reply-text">Admin has not replied yet. Please check back later.</div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
