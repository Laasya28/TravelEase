<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set correct timezone
require_once 'config/db_connect.php';
require_once 'config/email_config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    try {
        // Set timezone for MySQL session
        $pdo->exec("SET time_zone = '+05:30'");
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_verified = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Mark all previous tokens as used
                $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ?");
                $stmt->execute([$email]);
                
                // Save new token in database
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires]);
                
                // Commit transaction
                $pdo->commit();
                
                // Send email using PHPMailer
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = SMTP_PORT;
                    
                    // Recipients
                    $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
                    $mail->addAddress($email);
                    
                    // Content
                    $resetLink = "http://{$_SERVER['HTTP_HOST']}/INT220/reset_password.php?token=" . urlencode($token);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request - TravelEase';
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #2c3e50;'>Password Reset Request</h2>
                            <p>Hello,</p>
                            <p>We received a request to reset your password. Click the button below to reset it. This link will expire in 1 hour.</p>
                            <p style='text-align: center; margin: 30px 0;'>
                                <a href='$resetLink' style='
                                    background: linear-gradient(to right, #3498db, #2ecc71);
                                    color: white;
                                    text-decoration: none;
                                    padding: 12px 25px;
                                    border-radius: 5px;
                                    font-weight: bold;
                                    display: inline-block;
                                '>Reset Password</a>
                            </p>
                            <p>If you didn't request this, you can safely ignore this email.</p>
                            <p>Best regards,<br>TravelEase Team</p>
                        </div>
                    ";
                    $mail->AltBody = "Reset your password by clicking this link: $resetLink";
                    
                    $mail->send();
                    $success = 'Password reset instructions have been sent to your email.';
                } catch (Exception $e) {
                    error_log("Email Error: " . $e->getMessage());
                    $error = "Could not send reset instructions. Please try again later.";
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Transaction Error: " . $e->getMessage());
                $error = "Could not process reset request. Please try again later.";
            }
        } else {
            // Don't reveal if user exists or not
            $success = 'If your email is registered and verified, you will receive password reset instructions.';
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - TravelEase</title>
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
        
        .forgot-container {
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
    <div class="forgot-container">
        <h2 class="text-center mb-4">Forgot Password</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <small class="text-muted">Enter your registered email address</small>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
            <div class="text-center">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
