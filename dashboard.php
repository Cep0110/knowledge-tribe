 <?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$allowed_sort = ['first_name', 'last_name', 'email', 'created_at'];
if (!in_array($sort, $allowed_sort)) $sort = 'created_at';
if ($order !== 'ASC' && $order !== 'DESC') $order = 'DESC';

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($role === 'admin') {
    $query = "SELECT s.*, u.username, u.email FROM students s JOIN users u ON s.user_id = u.id";
    $params = [];
    if (!empty($search)) {
        $query .= " WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ?";
        $params = ["%$search%", "%$search%", "%$search%"];
    }
    $query .= " ORDER BY $sort $order";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT s.*, u.username, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Knowledge Tribe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #ff7200; color: white; }
        .actions a { margin-right: 10px; text-decoration: none; color: blue; }
        .search-bar { margin-bottom: 20px; }
        .search-bar input { padding: 8px; width: 300px; }
        .search-bar button { padding: 8px; }
    </style>
</head>
<body>
    <header style="height: auto; padding: 20px;">
        <nav style="position: relative; padding: 0;">
            <img src="image/logo.png" alt="Logo" style="height: 50px;">
            <ul style="margin-left: auto;">
                <li><a href="index.html">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo ucfirst($role); ?>)</h1>
    </header>

    <div class="dashboard-container">
        <?php if ($role === 'admin'): ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Student Records</h2>
                <div class="admin-actions">
                    <a href="export_csv.php" class="cta-button" style="background: green; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">Export CSV (Excel)</a>
                    <a href="import_csv.php" class="cta-button" style="background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-left: 10px;">Import CSV</a>
                    <a href="report_gen.php" class="cta-button" style="background: purple; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-left: 10px;">Generate Reports</a>
                </div>
            </div>
            <div class="search-bar" style="margin-top: 20px;">
                <form action="dashboard.php" method="GET">
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                    <a href="dashboard.php" style="margin-left: 10px;">Reset</a>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th><a href="?sort=first_name&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">First Name</a></th>
                        <th><a href="?sort=last_name&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Last Name</a></th>
                        <th><a href="?sort=email&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Email</a></th>
                        <th>Phone</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <?php if ($s['image']): ?>
                                    <img src="uploads/<?php echo $s['image']; ?>" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%;">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($s['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['email']); ?></td>
                            <td><?php echo htmlspecialchars($s['phone']); ?></td>
                            <td><?php echo htmlspecialchars($s['country']); ?></td>
                            <td class="actions">
                                <a href="edit_student.php?id=<?php echo $s['id']; ?>">Edit</a>
                                <a href="delete_student.php?id=<?php echo $s['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <h2>My Profile</h2>
            <div style="margin-bottom: 20px;">
                <?php if ($student['image']): ?>
                    <img src="uploads/<?php echo $student['image']; ?>" alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 2px solid #ff7200;">
                <?php endif; ?>
            </div>
            <table>
                <tr><th>First Name</th><td><?php echo htmlspecialchars($student['first_name']); ?></td></tr>
                <tr><th>Last Name</th><td><?php echo htmlspecialchars($student['last_name']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
                <tr><th>DOB</th><td><?php echo htmlspecialchars($student['dob']); ?></td></tr>
                <tr><th>Gender</th><td><?php echo htmlspecialchars($student['gender']); ?></td></tr>
                <tr><th>Phone</th><td><?php echo htmlspecialchars($student['phone']); ?></td></tr>
                <tr><th>Address</th><td><?php echo htmlspecialchars($student['address']); ?></td></tr>
                <tr><th>Hobbies</th><td><?php echo htmlspecialchars($student['hobbies']); ?></td></tr>
            </table>
            <div style="margin-top: 20px;">
                <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="cta-button">Edit My Profile</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
