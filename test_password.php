<?php
require_once 'config/db_connect.php';

// Test password
$test_password = "test123";  // The password you're trying to use
$email = "sapnarai2005@gmail.com";

try {
    // Get current password hash
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Testing password verification:\n";
        echo "Email: " . $email . "\n";
        echo "Current password hash: " . $user['password'] . "\n";
        
        // Try verifying with different variations
        $test_cases = [
            $test_password,
            trim($test_password),
            rtrim($test_password),
            ltrim($test_password)
        ];
        
        foreach ($test_cases as $i => $pwd) {
            $result = password_verify($pwd, $user['password']);
            echo "\nTest case " . ($i + 1) . ":\n";
            echo "Password: '" . $pwd . "'\n";
            echo "Length: " . strlen($pwd) . "\n";
            echo "Verification result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
        }
        
        // Create a new hash
        echo "\nCreating new hash:\n";
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "New hash: " . $new_hash . "\n";
        
        // Verify with new hash
        $verify = password_verify($test_password, $new_hash);
        echo "Verification with new hash: " . ($verify ? "SUCCESS" : "FAILED") . "\n";
        
        // Update password in database
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$new_hash, $email]);
        echo "\nPassword updated in database. Try logging in with: $test_password\n";
        
    } else {
        echo "User not found\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
