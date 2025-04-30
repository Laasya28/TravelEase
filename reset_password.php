<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set correct timezone
require_once 'config/db_connect.php';

$error = '';
$success = '';
$token = '';

// Set timezone for MySQL session
try {
    $pdo->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    error_log("Failed to set timezone: " . $e->getMessage());
}

// Validate token
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
} elseif (isset($_POST['token'])) {
    $token = trim($_POST['token']);
} else {
    header('Location: forgot_password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if token is valid and not expired
            $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
            $stmt->execute([$token]);
            $reset = $stmt->fetch();
            
            if ($reset) {
                error_log("Reset found for email: " . $reset['email']);
                
                // Get the user's record
                $user_stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $user_stmt->execute([$reset['email']]);
                $user = $user_stmt->fetch();
                
                if ($user) {
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    try {
                        // Hash the new password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        error_log("New password hash: " . $hashed_password);
                        
                        // Update the password
                        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                        $update_stmt->execute([$hashed_password, $reset['email']]);
                        
                        // Mark ALL tokens for this email as used
                        $token_stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ?");
                        $token_stmt->execute([$reset['email']]);
                        
                        // Verify the password update
                        $verify_stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
                        $verify_stmt->execute([$reset['email']]);
                        $updated_user = $verify_stmt->fetch();
                        
                        // Test password verification
                        if (password_verify($password, $updated_user['password'])) {
                            error_log("Password verification test successful");
                            $pdo->commit();
                            $success = 'Your password has been reset successfully. You can now <a href="login.php">login</a> with your new password.';
                        } else {
                            error_log("Password verification test failed");
                            throw new Exception("Password verification test failed");
                        }
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        error_log("Transaction Error: " . $e->getMessage());
                        $error = 'An error occurred while resetting your password.';
                    }
                } else {
                    error_log("User not found for email: " . $reset['email']);
                    $error = 'User not found.';
                }
            } else {
                error_log("No valid reset token found");
                $error = 'Invalid or expired reset token. Please request a new password reset link.';
            }
        } catch (PDOException $e) {
            error_log("Reset Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
} else {
    // Verify token exists and is valid
    try {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
        $stmt->execute([$token]);
        if (!$stmt->fetch()) {
            error_log("No valid reset token found");
            $error = 'Invalid or expired reset token. Please request a new password reset link.';
        }
    } catch (PDOException $e) {
        error_log("Token Check Error: " . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - TravelEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .reset-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.8);
            border: none;
            padding: 0.8rem;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #3498db, #2ecc71);
            border: none;
            padding: 0.8rem;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #2980b9, #27ae60);
        }
        
        .alert {
            background: rgba(255, 255, 255, 0.8);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2 class="text-center mb-4">Reset Password</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php if (strpos($error, 'Invalid or expired') !== false): ?>
                <div class="text-center">
                    <a href="forgot_password.php" class="btn btn-primary">Request New Reset Link</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <div class="text-center">
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </div>
        <?php elseif (!$error || strpos($error, 'Invalid or expired') === false): ?>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    <small class="text-muted">Password must be at least 6 characters long</small>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
