<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

$id = $_GET['id'];

try {
    $pdo->beginTransaction();

    // Get user_id to delete from users table as well
    $stmt = $pdo->prepare("SELECT user_id FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if ($student) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$student['user_id']]);
    }

    $pdo->commit();
    header("Location: dashboard.php");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error deleting record: " . $e->getMessage());
}
?>
