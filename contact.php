<?php
session_start();
require_once 'config/db_connect.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    try {
        // Save message to database
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $subject, $message])) {
            
            // Send confirmation email
            $mail = new PHPMailer(true);
            
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
            $mail->isHTML(true);
            $mail->Subject = 'Thank you for contacting TravelEase';
            $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #2c3e50;'>Message Received</h2>
                    <p>Dear {$name},</p>
                    <p>Thank you for contacting TravelEase. We have received your message and will get back to you shortly.</p>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h3 style='color: #2c3e50; margin-top: 0;'>Your Message Details:</h3>
                        <p><strong>Subject:</strong> {$subject}</p>
                        <p><strong>Message:</strong><br>{$message}</p>
                    </div>
                    <p>Our team will review your message and respond within 24-48 hours.</p>
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #666;'>
                        <p>Best regards,<br>The TravelEase Team</p>
                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            $success = "Thank you! Your message has been sent successfully.";
            
        } else {
            throw new Exception("Failed to save your message");
        }
    } catch (Exception $e) {
        $error = "Sorry, there was an error sending your message. Please try again later.";
        error_log("Contact Form Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - TravelEase</title>
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
            font-family: 'Segoe UI', Arial, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            pointer-events: none;
            animation: float 8s infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }

        .contact-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 800px;
            margin: 50px auto;
            position: relative;
            overflow: hidden;
        }

        .contact-container::before {
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

        textarea ~ .form-label {
            top: 30px;
        }

        .form-control:focus + .form-label,
        .form-control:not(:placeholder-shown) + .form-label {
            top: 0;
            font-size: 12px;
            color: #3498db;
            font-weight: 600;
        }

        textarea:focus + .form-label,
        textarea:not(:placeholder-shown) + .form-label {
            top: -10px;
        }

        .btn-submit {
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
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="animated-background" id="animatedBackground"></div>

    <div class="container">
        <div class="contact-container">
            <h2 class="text-center mb-4" style="color: var(--text);">Contact Us</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="contact.php" id="contactForm">
                <div class="form-group">
                    <input type="text" class="form-control" id="name" name="name" placeholder=" " required>
                    <label class="form-label" for="name">Your Name</label>
                </div>

                <div class="form-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder=" " required>
                    <label class="form-label" for="email">Email Address</label>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" id="subject" name="subject" placeholder=" " required>
                    <label class="form-label" for="subject">Subject</label>
                </div>

                <div class="form-group">
                    <textarea class="form-control" id="message" name="message" rows="5" placeholder=" " required></textarea>
                    <label class="form-label" for="message">Your Message</label>
                </div>

                <button type="submit" class="btn-submit">
                    Send Message
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create animated background
        function createParticles() {
            const background = document.getElementById('animatedBackground');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random size between 5 and 20 pixels
                const size = Math.random() * 15 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random starting position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                
                // Random animation duration between 5 and 15 seconds
                particle.style.animationDuration = `${Math.random() * 10 + 5}s`;
                
                // Random delay
                particle.style.animationDelay = `${Math.random() * 5}s`;
                
                background.appendChild(particle);
                
                // Remove and recreate particle after animation
                particle.addEventListener('animationend', () => {
                    particle.remove();
                    createParticle();
                });
            }
        }

        function createParticle() {
            const background = document.getElementById('animatedBackground');
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            const size = Math.random() * 15 + 5;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = '100%';
            
            particle.style.animationDuration = `${Math.random() * 10 + 5}s`;
            
            background.appendChild(particle);
            
            particle.addEventListener('animationend', () => {
                particle.remove();
                createParticle();
            });
        }

        // Initialize particles
        document.addEventListener('DOMContentLoaded', createParticles);

        // Form validation and animation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            const inputs = form.querySelectorAll('.form-control');

            inputs.forEach(input => {
                // Add animation when input is focused
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                // Remove animation when input loses focus
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });

                // Keep label up if input has value
                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
