  <?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Set cookie for last login (optional)
        setcookie("last_login_user", $user['username'], time() + (86400 * 30), "/");

        // Redirect based on role (optional differentiation)
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Knowledge Tribe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .error { 
            color: #dc3545; 
            background: #f8d7da; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 15px;
            text-align: center;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 5px;
            font-size: 16px;
        }
        .btn-login {
            width: 100%;
            background: #ff7200;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-login:hover { background: #e66600; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Login to Knowledge Tribe</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username (admin or your student username)" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; font-size: 14px; color: #666;">
            <p>Don't have an account? <a href="index.html#registration" style="color: #ff7200;">Register here</a></p>
            <p style="margin-top: 10px; font-size: 12px; color: #999;">
                Default Admin: admin / Admin123
            </p>
        </div>
    </div>
</body>
</html>
