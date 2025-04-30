<?php
session_start();
require_once 'config/db_connect.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    try {
        // Debug information
        error_log("Login attempt for email: " . $email);
        error_log("Password length: " . strlen($password));
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            error_log("User found, stored password hash: " . $user['password']);
            error_log("Verifying provided password...");
            
            // Test both trimmed and untrimmed password
            $passwords_to_try = [
                $password,
                trim($password)
            ];
            
            $password_verified = false;
            foreach ($passwords_to_try as $pwd) {
                if (password_verify($pwd, $user['password'])) {
                    $password_verified = true;
                    error_log("Password verified with" . (strlen($pwd) !== strlen($password) ? " trimmed" : "") . " password");
                    break;
                }
            }
            
            if ($password_verified) {
                error_log("Password verified successfully");
                if (!$user['is_verified']) {
                    error_log("User not verified");
                    $error = 'Please verify your email address before logging in.';
                } else {
                    error_log("User verified, setting session");
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    error_log("Session set, redirecting to index.php");
                    header('Location: index.php');
                    exit;
                }
            } else {
                error_log("Password verification failed");
                error_log("Provided password length: " . strlen($password));
                error_log("Hash info: " . password_get_info($user['password'])['algo']);
                $error = 'Invalid email or password';
            }
        } else {
            error_log("User not found");
            $error = 'Invalid email or password';
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
    }
}

// Debug: Check if session exists
if (isset($_SESSION['user_id'])) {
    error_log("Session exists: " . $_SESSION['user_id']);
} else {
    error_log("No session exists");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TravelEase</title>
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

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
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
            width: 100%;
            margin-top: 20px;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }

        .btn-login:active {
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

        .divider {
            margin: 20px 0;
            text-align: center;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            background: #ffffff;
            padding: 0 10px;
            color: #95a5a6;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4" style="color: var(--text);">Welcome Back!</h2>
        
        <?php if ($error): ?>
            <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php">
            <div class="form-group">
                <input type="email" class="form-control" id="email" name="email" placeholder=" " required>
                <label class="form-label" for="email">Email Address</label>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder=" " required>
                <label class="form-label" for="password">Password</label>
            </div>

            <button type="submit" class="btn-login">
                Sign In
            </button>

            <div class="links">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <div class="divider">
                <span>OR</span>
            </div>

            <div class="links">
                Don't have an account? <a href="register.php">Sign Up</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const inputs = form.querySelectorAll('.form-control');

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

                // Check initial state
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

                    if (input.type === 'email' && input.value.trim()) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(input.value)) {
                            isValid = false;
                            input.classList.add('is-invalid');
                            errorMessages.push('Please enter a valid email address');
                        }
                    }
                });

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
