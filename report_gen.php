<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

require_once 'db_connect.php';

// Summary Stats
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$maleCount = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'male'")->fetchColumn();
$femaleCount = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'female'")->fetchColumn();

// Course stats
$courseStats = $pdo->query("SELECT c.name, COUNT(sc.student_id) as student_count 
                            FROM courses c 
                            LEFT JOIN student_courses sc ON c.id = sc.course_id 
                            GROUP BY c.id")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Reports - Knowledge Tribe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-container { padding: 40px; background: white; max-width: 900px; margin: 20px auto; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .stat-box { display: inline-block; width: 30%; padding: 20px; background: #f9f9f9; border-radius: 8px; text-align: center; margin-right: 2%; }
        .stat-box h3 { font-size: 24px; color: #ff7200; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .report-container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <h2 style="text-align: center;">Knowledge Tribe Company - Registration Report</h2>
        <p style="text-align: center; color: #666;">Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        
        <div style="margin-top: 30px;">
            <div class="stat-box">
                <p>Total Registered</p>
                <h3><?php echo $totalStudents; ?></h3>
            </div>
            <div class="stat-box">
                <p>Male Students</p>
                <h3><?php echo $maleCount; ?></h3>
            </div>
            <div class="stat-box">
                <p>Female Students</p>
                <h3><?php echo $femaleCount; ?></h3>
            </div>
        </div>

        <h3 style="margin-top: 40px;">Course Enrollment Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Students Enrolled</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courseStats as $stat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stat['name']); ?></td>
                        <td><?php echo $stat['student_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()" style="background: purple; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer;">Print to PDF</button>
            <a href="dashboard.php" style="margin-left: 20px;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
