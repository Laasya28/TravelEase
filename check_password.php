<?php
require_once 'config/db_connect.php';

try {
    $email = 'sapnarai2005@gmail.com'; // Your email
    
    // Check user record
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Password Hash: " . $user['password'] . "\n";
        echo "Is Verified: " . ($user['is_verified'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "User not found\n";
    }
    
    // Check password resets
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$email]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reset) {
        echo "\nLatest Password Reset:\n";
        echo "Token: " . $reset['token'] . "\n";
        echo "Created: " . $reset['created_at'] . "\n";
        echo "Expires: " . $reset['expires_at'] . "\n";
        echo "Used: " . ($reset['used'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "\nNo password reset records found\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
