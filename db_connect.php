<?php
$host = 'localhost';
$db   = 'knowledge_tribe';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => true, // Optimized performance by reusing connections
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Display success only if the script is accessed directly for testing
     if (basename($_SERVER['PHP_SELF']) == 'db_connect.php') {
         echo "<h2>Database Status: <span style='color:green;'>Connected Successfully</span></h2>";
     }
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
