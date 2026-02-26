<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

require_once 'db_connect.php';

$filename = "student_data_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email', 'DOB', 'Gender', 'Phone', 'Country', 'Registration Date'));

// Fetch student records joined with user email
$stmt = $pdo->prepare("SELECT s.id, s.first_name, s.last_name, u.email, s.dob, s.gender, s.phone, s.country, s.created_at 
                       FROM students s 
                       JOIN users u ON s.user_id = u.id");
$stmt->execute();
$records = $stmt->fetchAll();

foreach ($records as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
