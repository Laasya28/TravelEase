<?php
session_start();
require_once 'config/db_connect.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Find user with this token
        $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update user as verified
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                $success = "Email verified successfully! You can now login to your account.";
            } else {
                $error = "Failed to verify email. Please try again.";
            }
        } else {
            $error = "Invalid or expired verification token.";
        }
    } catch (PDOException $e) {
        error_log("Email Verification Error: " . $e->getMessage());
        $error = "An error occurred. Please try again later.";
    }
} else {
    $error = "No verification token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - TravelEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --text: #2c3e50;
            --background: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .verify-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .verify-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #3498db, #2ecc71);
        }

        .status-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .success .status-icon {
            color: #2ecc71;
        }

        .error .status-icon {
            color: #e74c3c;
        }

        .message {
            font-size: 18px;
            margin-bottom: 30px;
            color: var(--text);
        }

        .btn-login {
            background: linear-gradient(to right, #3498db, #2ecc71);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="verify-container <?php echo $success ? 'success' : 'error'; ?>">
        <div class="status-icon">
            <?php echo $success ? '✓' : '✕'; ?>
        </div>
        <div class="message">
            <?php echo $success ? $success : $error; ?>
        </div>
        <a href="login.php" class="btn-login">
            Go to Login
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
