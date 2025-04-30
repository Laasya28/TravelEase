<?php
require_once 'config/db_connect.php';

try {
    // Check MySQL timezone
    $stmt = $pdo->query("SELECT @@system_time_zone, @@global.time_zone, @@session.time_zone, NOW()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "System timezone: " . $result['@@system_time_zone'] . "\n";
    echo "Global timezone: " . $result['@@global.time_zone'] . "\n";
    echo "Session timezone: " . $result['@@session.time_zone'] . "\n";
    echo "Current MySQL time: " . $result['NOW()'] . "\n";
    
    // Check PHP timezone
    echo "\nPHP timezone: " . date_default_timezone_get() . "\n";
    echo "PHP time: " . date('Y-m-d H:i:s') . "\n";
    
    // Check active reset tokens
    $stmt = $pdo->query("SELECT * FROM password_resets WHERE used = 0 ORDER BY created_at DESC");
    $resets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nActive reset tokens:\n";
    foreach ($resets as $reset) {
        echo "Token: " . substr($reset['token'], 0, 10) . "...\n";
        echo "Email: " . $reset['email'] . "\n";
        echo "Created: " . $reset['created_at'] . "\n";
        echo "Expires: " . $reset['expires_at'] . "\n";
        echo "Used: " . ($reset['used'] ? 'Yes' : 'No') . "\n\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
