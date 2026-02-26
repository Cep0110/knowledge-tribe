<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

$id = $_GET['id'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Check if student exists and if the user has permission to edit
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student || ($role !== 'admin' && $student['user_id'] != $user_id)) {
    die("Access denied or student not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);

    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$firstName, $lastName, $phone, $id]);

    // Update email in users table
    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->execute([$email, $student['user_id']]);

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - Knowledge Tribe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div style="max-width: 600px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px;">
        <h2>Edit Student Details</h2>
        <form action="edit_student.php?id=<?php echo $id; ?>" method="POST">
            <label>First Name:</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
            <label>Last Name:</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
            <button type="submit" style="background: #ff7200; color: white; padding: 10px; border: none; cursor: pointer;">Update</button>
            <a href="dashboard.php" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
</body>
</html>
