<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (($handle = fopen($file, "r")) !== FALSE) {
        // Skip header
        fgetcsv($handle);
        
        $pdo->beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Assuming CSV structure matches Export:
                // ID, First Name, Last Name, Email, DOB, Gender, Phone, Country, CreatedAt
                // Let's assume we are importing new students (without ID)
                // New CSV format for Import: FirstName, LastName, Email, DOB, Gender, Phone, Country
                
                $firstName = $data[0];
                $lastName = $data[1];
                $email = $data[2];
                $dob = $data[3];
                $gender = strtolower($data[4]);
                $phone = $data[5];
                $country = $data[6];

                // Create a User first
                $username = strtolower($firstName . "." . $lastName . rand(10, 99));
                $password = password_hash("Tribe123", PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'student')");
                $stmt->execute([$username, $password, $email]);
                $userId = $pdo->lastInsertId();

                // Create Student
                $stmt = $pdo->prepare("INSERT INTO students (user_id, first_name, last_name, dob, gender, phone, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $firstName, $lastName, $dob, $gender, $phone, $country]);
            }
            $pdo->commit();
            echo "<script>alert('Data Imported Successfully!'); window.location.href='dashboard.php';</script>";
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error importing data: " . $e->getMessage());
        }
        fclose($handle);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Students - Knowledge Tribe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div style="max-width: 500px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px;">
        <h2>Import Students from CSV</h2>
        <p>CSV format: FirstName, LastName, Email, DOB, Gender, Phone, Country</p>
        <form action="import_csv.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit" style="background: #ff7200; color: white; padding: 10px; border: none; cursor: pointer; margin-top: 10px;">Upload & Import</button>
            <a href="dashboard.php" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
</body>
</html>
