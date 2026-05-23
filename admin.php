<?php
session_start();
require_once 'functions.php';
initDataFile();

// Simple admin authentication (you can add password protection)
$adminPassword = 'admin123'; // Change this to your desired password

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
        if ($_POST['admin_password'] === $adminPassword) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $error = 'Invalid password';
        }
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login</title>
            <link rel="stylesheet" href="style.css">
            <style>
                .login-container {
                    max-width: 400px;
                    margin: 100px auto;
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                }
                .login-container h2 {
                    text-align: center;
                    margin-bottom: 30px;
                    color: #333;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h2>🔐 Admin Login</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error" style="display:block; margin-bottom:20px;"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Enter Admin Password</label>
                        <input type="password" name="admin_password" required>
                    </div>
                    <button type="submit" class="btn-submit">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_complaint'])) {
    $complaintId = $_POST['complaint_id'];
    $reply = trim($_POST['reply']);
    
    if (!empty($reply)) {
        updateComplaintReply($complaintId, $reply);
        $success = 'Reply sent successfully!';
    }
}

$complaints = getAllComplaints();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Complaint Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="admin-container">
            <div class="admin-header">
                <h1>🔧 Admin Dashboard</h1>
                <div>
                    <a href="admin.php?logout=1" class="back-btn" style="margin-right:10px;">Logout</a>
                    <a href="index.php" class="back-btn">Back to Website</a>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
            <div class="alert alert-success" style="display:block; margin-bottom:20px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout'])): ?>
            <?php session_destroy(); header('Location: admin.php'); exit; ?>
            <?php endif; ?>
            
            <h2 style="margin-bottom: 20px;">All Complaints (<?php echo count($complaints); ?>)</h2>
            
            <?php if (empty($complaints)): ?>
                <div class="no-complaints">No complaints submitted yet.</div>
            <?php else: ?>
                <div class="complaint-table">
                    <?php foreach ($complaints as $complaint): ?>
                    <div class="complaint-item">
                        <div class="complaint-info">
                            <div class="info-row">
                                <span class="info-label">ID:</span>
                                <strong><?php echo htmlspecialchars($complaint['id']); ?></strong>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Name:</span>
                                <?php echo htmlspecialchars($complaint['name']); ?>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <?php echo htmlspecialchars($complaint['email']); ?>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Date:</span>
                                <?php echo htmlspecialchars($complaint['created_at']); ?>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Complaint:</span>
                                <div style="margin-top: 5px; padding: 10px; background: white; border-radius: 8px;">
                                    <?php echo nl2br(htmlspecialchars($complaint['complaint'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($complaint['admin_reply']): ?>
                        <div class="existing-reply">
                            <strong>Previous Reply:</strong><br>
                            <?php echo nl2br(htmlspecialchars($complaint['admin_reply'])); ?>
                            <br><small>Replied on: <?php echo htmlspecialchars($complaint['replied_at']); ?></small>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="reply-form">
                            <input type="hidden" name="complaint_id" value="<?php echo htmlspecialchars($complaint['id']); ?>">
                            <textarea name="reply" class="reply-textarea" rows="3" placeholder="Write your reply here..."></textarea>
                            <button type="submit" name="reply_complaint" class="btn-reply">Send Reply</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
