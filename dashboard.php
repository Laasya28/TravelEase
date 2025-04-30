<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - TravelEase</title>
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
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary);
        }

        .nav-link {
            color: var(--text);
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent);
        }

        .welcome-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-top: 50px;
            position: relative;
            overflow: hidden;
        }

        .welcome-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #3498db, #2ecc71);
        }

        .btn-custom {
            background: linear-gradient(to right, #3498db, #2ecc71);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }

        .card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">TravelEase</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Destinations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                    <a href="logout.php" class="btn-custom">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-container">
            <h1 class="text-center mb-4">Welcome to TravelEase</h1>
            <p class="text-center mb-5">Discover amazing destinations and plan your next adventure with us.</p>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="https://source.unsplash.com/random/800x600/?beach" class="card-img-top" alt="Beach">
                        <div class="card-body">
                            <h5 class="card-title">Beach Getaways</h5>
                            <p class="card-text">Explore beautiful beaches and coastal destinations.</p>
                            <a href="#" class="btn-custom">Learn More</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="https://source.unsplash.com/random/800x600/?mountain" class="card-img-top" alt="Mountain">
                        <div class="card-body">
                            <h5 class="card-title">Mountain Adventures</h5>
                            <p class="card-text">Discover breathtaking mountain landscapes.</p>
                            <a href="#" class="btn-custom">Learn More</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="https://source.unsplash.com/random/800x600/?city" class="card-img-top" alt="City">
                        <div class="card-body">
                            <h5 class="card-title">City Escapes</h5>
                            <p class="card-text">Experience vibrant city life and culture.</p>
                            <a href="#" class="btn-custom">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
