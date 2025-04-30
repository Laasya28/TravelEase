<?php
session_start();
require_once 'config/db_connect.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // Validate password match
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        // Validate password strength
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Email already registered');
        }

        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_token) VALUES (?, ?, ?, ?)");
        if (!$stmt->execute([$name, $email, $hashed_password, $verification_token])) {
            throw new Exception('Failed to create account');
        }

        // Send verification email
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sapnarai2005@gmail.com';
            $mail->Password = 'eahq zagr cwio yjij';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('sapnarai2005@gmail.com', 'TravelEase');
            $mail->addAddress($email, $name);
            
            // Content
            $verification_link = "http://{$_SERVER['HTTP_HOST']}/INT220/verify.php?token=" . urlencode($verification_token);
            
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your TravelEase Account';
            $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2c3e50;'>Welcome to TravelEase!</h2>
                    <p>Hello {$name},</p>
                    <p>Thank you for registering with TravelEase. Please click the button below to verify your email address:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$verification_link}' 
                           style='background: linear-gradient(to right, #3498db, #2ecc71);
                                  color: white;
                                  text-decoration: none;
                                  padding: 12px 24px;
                                  border-radius: 5px;
                                  font-weight: bold;
                                  display: inline-block;'>
                            Verify Email
                        </a>
                    </p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p style='background: #f8f9fa; padding: 12px; border-radius: 4px; word-break: break-all;'>
                        {$verification_link}
                    </p>
                    <p>This link will expire in 24 hours.</p>
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666;'>
                        <p>Best regards,<br>The TravelEase Team</p>
                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            
            $_SESSION['register_success'] = true;
            header('Location: login.php?registered=true');
            exit;
            
        } catch (Exception $e) {
            throw new Exception('Failed to send verification email. Please try again.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TravelEase</title>
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

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #3498db, #2ecc71);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-control {
            background: #ffffff;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            color: #2c3e50;
            transition: all 0.3s ease;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 15px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        .form-label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            background: #ffffff;
            padding: 0 5px;
            color: #95a5a6;
            font-size: 14px;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .form-control:focus + .form-label,
        .form-control:not(:placeholder-shown) + .form-label {
            top: 0;
            font-size: 12px;
            color: #3498db;
            font-weight: 600;
        }

        .btn-register {
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
            width: 100%;
            margin-top: 20px;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }

        .btn-register:active {
            transform: translateY(-1px);
        }

        .links {
            margin-top: 20px;
            text-align: center;
        }

        .links a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .links a:hover {
            color: #2980b9;
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            color: #95a5a6;
        }

        .password-strength.weak { color: #e74c3c; }
        .password-strength.medium { color: #f39c12; }
        .password-strength.strong { color: #27ae60; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 class="text-center mb-4" style="color: var(--text);">Create Account</h2>
        
        <?php if ($error): ?>
            <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="register.php">
            <div class="form-group">
                <input type="text" class="form-control" id="name" name="name" placeholder=" " required>
                <label class="form-label" for="name">Full Name</label>
            </div>

            <div class="form-group">
                <input type="email" class="form-control" id="email" name="email" placeholder=" " required>
                <label class="form-label" for="email">Email Address</label>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder=" " required>
                <label class="form-label" for="password">Password</label>
                <div class="password-strength"></div>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder=" " required>
                <label class="form-label" for="confirm_password">Confirm Password</label>
            </div>

            <button type="submit" class="btn-register">
                Create Account
            </button>

            <div class="links">
                Already have an account? <a href="login.php">Sign In</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const inputs = form.querySelectorAll('.form-control');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordStrength = document.querySelector('.password-strength');

            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;
                
                switch(strength) {
                    case 0:
                    case 1:
                        return ['Weak', 'weak'];
                    case 2:
                    case 3:
                        return ['Medium', 'medium'];
                    case 4:
                    case 5:
                        return ['Strong', 'strong'];
                    default:
                        return ['Weak', 'weak'];
                }
            }

            password.addEventListener('input', function() {
                const [text, className] = checkPasswordStrength(this.value);
                passwordStrength.textContent = `Password Strength: ${text}`;
                passwordStrength.className = `password-strength ${className}`;
            });

            // Input animations
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });

                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const errorMessages = [];

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        errorMessages.push(`${input.name} is required`);
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (password.value !== confirmPassword.value) {
                    isValid = false;
                    errorMessages.push('Passwords do not match');
                    password.classList.add('is-invalid');
                    confirmPassword.classList.add('is-invalid');
                }

                const [strengthText] = checkPasswordStrength(password.value);
                if (strengthText === 'Weak') {
                    isValid = false;
                    errorMessages.push('Password is too weak. Include uppercase, lowercase, numbers, and special characters');
                }

                if (!isValid) {
                    e.preventDefault();
                    const errorMessage = document.querySelector('.error-message') || document.createElement('div');
                    errorMessage.className = 'error-message show';
                    errorMessage.textContent = errorMessages.join('. ');
                    if (!document.querySelector('.error-message')) {
                        form.insertBefore(errorMessage, form.firstChild);
                    }
                }
            });
        });
    </script>
</body>
</html>
