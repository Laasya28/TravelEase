<?php
require_once 'config/db_connect.php';
require_once 'config/email_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $destination = htmlspecialchars($_POST['destination']);
    $travel_date = $_POST['travel_date'];
    $num_people = (int)$_POST['num_people'];
    $total_amount = (float)$_POST['total_amount'];
    $booking_token = bin2hex(random_bytes(32));

    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_email, destination, travel_date, num_people, total_amount, booking_token) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, $destination, $travel_date, $num_people, $total_amount, $booking_token]);

        // Send confirmation email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmation - TravelEase';
        $mail->Body = "
            <h2>Booking Confirmation</h2>
            <p>Thank you for booking with TravelEase!</p>
            <p>Booking Details:</p>
            <ul>
                <li>Destination: $destination</li>
                <li>Travel Date: $travel_date</li>
                <li>Number of People: $num_people</li>
                <li>Total Amount: $$total_amount</li>
            </ul>
            <p>Your booking token is: $booking_token</p>
            <p>Please keep this token for future reference.</p>
            <p>For more please visit contact page</p>
        ";

        $mail->send();
        $success_message = "Booking successful! Check your email for confirmation.";
    } catch (Exception $e) {
        $error_message = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Journey - TravelEase</title>
    <script src="https://cdn.tailwindcss.com"></script>

<style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .animate-gradient {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="animate-gradient min-h-screen text-white">
    <!-- Navigation -->
    <!-- <nav class="glass-effect fixed w-full z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="text-2xl font-bold text-white">TravelEase</a>
                <div class="hidden md:flex space-x-8">
                    <a href="index.php" class="hover:text-gray-300 transition">Home</a>
                    <a href="destinations.php" class="hover:text-gray-300 transition">Destinations</a>
                    <a href="about.php" class="hover:text-gray-300 transition">About</a>
                    <a href="login.php" class="hover:text-gray-300 transition">Login</a>
                </div>
            </div>
        </div>
    </nav> -->
    <!-- Booking Form -->
    <div class="container mx-auto px-6 pt-32 pb-20">
        <div class="max-w-2xl mx-auto glass-effect p-8 rounded-lg">
            <h2 class="text-3xl font-bold mb-6 text-center">Let's Plan Your Journey!</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="bg-green-500 text-white p-4 rounded mb-6">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-500 text-white p-4 rounded mb-6">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="booking.php" method="POST" class="space-y-6">
                <div>
                    <label class="block mb-2">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full p-3 rounded bg-white/10 border border-white/20 focus:outline-none focus:border-white">
                </div>

                <div>
                    <!-- <label class="block mb-2">Destination</label> -->
                    <label class="block mb-2">Destination</label>
                    <select name="destination" required
                        class="w-full p-3 rounded bg-white/10 border border-white/20 focus:outline-none focus:border-white">
                        <option value="">Select Destination</option>
                        <option value="Paris" class="bg-blue-200">Paris</option>
                        <option value="Tokyo" class="bg-blue-200">Tokyo</option>
                        <option value="New York" class="bg-blue-200">New York</option>
                        <option value="Dubai" class="bg-blue-200">Dubai</option>
                        <option value="London" class="bg-blue-200">London</option>
                        <option value="Sydney" class="bg-blue-200">Sydney</option>  
                        <option value="Beijing" class="bg-blue-200">Beijing</option>
                        <option value="Andhra Pradesh" class="bg-blue-200">Andhra Pradesh</option>
                        <option value="Arunachal Pradesh" class="bg-blue-200">Arunachal Pradesh</option>
                        <option value="Assam" class="bg-blue-200">Assam</option>
                        <option value="Bihar" class="bg-blue-200">Bihar</option>
                        <option value="Chhattisgarh" class="bg-blue-200">Chhattisgarh</option>
                        <option value="Goa" class="bg-blue-200">Goa</option>
                        <option value="Gujarat" class="bg-blue-200">Gujarat</option>
                        <option value="Gandhinagar" class="bg-blue-200">Gandhinagar</option>
                        <option value="Haryana" class="bg-blue-200">Haryana</option>
                        <option value="Chandigarh" class="bg-blue-200">Chandigarh</option>
                        <option value="Himachal Pradesh" class="bg-blue-200">Himachal Pradesh</option>
                        <option value="Jharkhand" class="bg-blue-200">Jharkhand</option>
                        <option value="Karnataka" class="bg-blue-200">Karnataka</option>
                        <option value="Kerala" class="bg-blue-200">Kerala</option>
                        <option value="Madhya Pradesh" class="bg-blue-200">Madhya Pradesh</option>
                        <option value="Maharashtra" class="bg-blue-200">Maharashtra</option>
                        <option value="Manipur" class="bg-blue-200">Manipur</option>
                        <option value="Meghalaya" class="bg-blue-200">Meghalaya</option>
                        <option value="Mizoram" class="bg-blue-200">Mizoram</option>
                        <option value="Nagaland" class="bg-blue-200">Nagaland</option>
                        <option value="Odisha" class="bg-blue-200">Odisha</option>
                        <option value="Punjab" class="bg-blue-200">Punjab</option>
                        <option value="Rajasthan" class="bg-blue-200">Rajasthan</option>
                        <option value="Sikkim" class="bg-blue-200">Sikkim</option>
                        <option value="Tamil Nadu" class="bg-blue-200">Tamil Nadu</option>
                        <option value="Telangana" class="bg-blue-200">Telangana</option>
                        <option value="Tripura" class="bg-blue-200">Tripura</option>
                        <option value="Uttarakhand" class="bg-blue-200">Uttarakhand</option>
                        <option value="Uttar Pradesh" class="bg-blue-200">Uttar Pradesh</option>
                        <option value="West Bengal" class="bg-blue-200">West Bengal</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2">Travel Date</label>
                    <input type="date" name="travel_date" required min="<?php echo date('Y-m-d'); ?>"
                        class="w-full p-3 rounded bg-white/10 border border-white/20 focus:outline-none focus:border-white">
                </div>

                <div>
                    <label class="block mb-2">Number of People</label>
                    <input type="number" name="num_people" required min="1" max="50"
                        class="w-full p-3 rounded bg-white/10 border border-white/20 focus:outline-none focus:border-white">
                </div>

                <div>
                    <label class="block mb-2">Total Amount ($)</label>
                    <input type="number" name="total_amount" required min="0" step="0.01"
                        class="w-full p-3 rounded bg-white/10 border border-white/20 focus:outline-none focus:border-white">
                </div>

                <button type="submit"
                    class="w-full bg-white text-gray-900 py-3 px-6 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    Book Now
                </button>
            </form>
        </div>
    </div>

    <script>
        // Add client-side validation and dynamic pricing
        document.querySelector('select[name="destination"]').addEventListener('change', function() {
            const prices = {
                'Paris': 1200,
                'Tokyo': 1500,
                'New York': 1000,
                'Dubai': 1300,
                'London': 1100,
                'Sydney': 1400,
                'Beijing': 1600,
                "Andhra Pradesh": 1700,
                "Arunachal Pradesh": 1800,
                "Assam": 1900,
                "Bihar": 2000,
                "Chhattisgarh": 2100,
                "Goa": 2200,
                "Gujarat": 2300,
                "Gandhinagar": 2400,
                "Haryana": 2500,
                "Chandigarh": 2600,
                "Himachal Pradesh": 2700,
                "Jharkhand": 2800,
                "Karnataka": 2900,
                "Kerala": 3000,
                "Madhya Pradesh": 3100,
                "Maharashtra": 3200,
                "Manipur": 3300,
                "Meghalaya": 3400,
                "Mizoram": 3500,
                "Nagaland": 3600,
                "Odisha": 3700,
                "Punjab": 3800,
                "Rajasthan": 3900,
                "Sikkim": 4000,
                "Tamil Nadu": 4100,
                "Telangana": 4200,
                "Tripura": 4300,
                "Uttarakhand": 4400,
                "Uttar Pradesh": 4500,
                "West Bengal": 4600,
            };
            
            const numPeople = document.querySelector('input[name="num_people"]').value || 1;
            const basePrice = prices[this.value] || 0;
            document.querySelector('input[name="total_amount"]').value = (basePrice * numPeople).toFixed(2);
        });

        document.querySelector('input[name="num_people"]').addEventListener('change', function() {
            const destination = document.querySelector('select[name="destination"]').value;
            const prices = {
                'Paris': 1200,
                'Tokyo': 1500,
                'New York': 1000,
                'Dubai': 1300,
                'London': 1100,
                'Sydney': 1400,
                'Beijing': 1600,
                "Andhra Pradesh": 1700,
                "Arunachal Pradesh": 1800,
                "Assam": 1900,
                "Bihar": 2000,
                "Chhattisgarh": 2100,
                "Goa": 2200,
                "Gujarat": 2300,
                "Gandhinagar": 2400,
                "Haryana": 2500,
                "Chandigarh": 2600,
                "Himachal Pradesh": 2700,
                "Jharkhand": 2800,
                "Karnataka": 2900,
                "Kerala": 3000,
                "Madhya Pradesh": 3100,
                "Maharashtra": 3200,
                "Manipur": 3300,
                "Meghalaya": 3400,
                "Mizoram": 3500,
                "Nagaland": 3600,
                "Odisha": 3700,
                "Punjab": 3800,
                "Rajasthan": 3900,
                "Sikkim": 4000,
                "Tamil Nadu": 4100,
                "Telangana": 4200,
                "Tripura": 4300,
                "Uttarakhand": 4400,
                "Uttar Pradesh": 4500,
                "West Bengal": 4600,
            };
            
            const basePrice = prices[destination] || 0;
            document.querySelector('input[name="total_amount"]').value = (basePrice * this.value).toFixed(2);
        });
    </script>
</body>
</html>
