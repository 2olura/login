<?php
require 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'login';
$errors = [];
$success = '';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = clean($_POST['email']);
    $password = clean($_POST['password']);
    
    if(empty($email) || empty($password)) {
        $errors[] = "All fields are required";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Email or password is incorrect";
        }
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $phone = clean($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $gender = clean($_POST['gender']);
    
    if(empty($name)) {
        $errors[] = "Name is required";
    } elseif (!preg_match("/^[\p{L}\s']+$/u", $name)) {
        $errors[] = "Name should contain only letters and spaces";
    } elseif (strlen($name) < 3) {
        $errors[] = "Name should be at least 3 characters";
    } elseif (strlen($name) > 50) {
        $errors[] = "Name should not exceed 50 characters";
    }
    
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) 
        $errors[] = "Invalid email";
    
    if(empty($phone) || !validPhone($phone)) 
        $errors[] = "Invalid phone number";
    
    if(empty($password) || strlen($password) < 6) 
        $errors[] = "Password must be at least 6 characters";
    
    if($password != $confirm) 
        $errors[] = "Passwords do not match";
    
    if(empty($gender)) 
        $errors[] = "Select gender";
    
    if(empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if($stmt->rowCount() > 0) {
            $errors[] = "Email or phone already registered";
        }
    }
    
    if(empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, gender) VALUES (?, ?, ?, ?, ?)");
        
        if($stmt->execute([$name, $email, $phone, $hashed, $gender])) {
            $success = "Registration successful! You can login now";
            $page = 'login';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab <?php echo $page == 'login' ? 'active' : ''; ?>" onclick="window.location='?page=login'">Login</div>
            <div class="tab <?php echo $page == 'register' ? 'active' : ''; ?>" onclick="window.location='?page=register'">Register</div>
        </div>
        
        <?php if(!empty($errors)): ?>
            <div class="error"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if($page == 'login'): ?>
        <div class="form-box">
            <h2 style="text-align:center; margin-bottom:20px;">Login</h2>
            <form method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            <p style="text-align:center; margin-top:15px;">
                Don't have an account? <a href="?page=register">Register now</a>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if($page == 'register'): ?>
        <div class="form-box">
            <h2 style="text-align:center; margin-bottom:20px;">Create Account</h2>
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="name" placeholder="Full Name (letters only)" required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="text" name="phone" placeholder="Phone (9 digits)" maxlength="9" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password (min 6 characters)" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="input-group">
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
            <p style="text-align:center; margin-top:15px;">
                Already have an account? <a href="?page=login">Login</a>
            </p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
