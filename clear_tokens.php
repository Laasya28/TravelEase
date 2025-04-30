<?php
require_once 'config/db_connect.php';

try {
    $pdo->exec("DELETE FROM password_resets WHERE email = 'sapnarai2005@gmail.com'");
    echo "Old tokens cleared successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
