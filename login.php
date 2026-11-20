<?php

session_start();

require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM admins
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$username]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];

        header('Location: admin.php');
        exit;

    } else {

        $error = "Invalid username or password.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Airsell Cargo Login</title>

<style>

body{
    margin:0;
    background:#f4f6f9;
    font-family:Arial,sans-serif;
}

.login-container{
    width:400px;
    margin:100px auto;
    background:white;
    padding:30px;
    border-radius:8px;
    box-shadow:0 2px 15px rgba(0,0,0,.15);
}

h1{
    text-align:center;
    color:#003366;
    margin-bottom:25px;
}

input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:4px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:12px;
    background:#0066cc;
    color:white;
    border:none;
    cursor:pointer;
    border-radius:4px;
}

button:hover{
    background:#004fa3;
}

.error{
    background:#f8d7da;
    color:#721c24;
    padding:10px;
    margin-bottom:15px;
    border-radius:4px;
}

.logo{
    text-align:center;
    font-size:22px;
    font-weight:bold;
    color:#003366;
    margin-bottom:10px;
}

</style>

</head>

<body>

<div class="login-container">

<div class="logo">

🚚 Airsell Cargo

</div>

<h1>Admin Login</h1>

<?php if($error): ?>

<div class="error">

<?php echo $error; ?>

</div>

<?php endif; ?>

<form method="POST">

    <input
        type="text"
        name="username"
        placeholder="Username"
        required
    >

    <input
        type="password"
        name="password"
        placeholder="Password"
        required
    >

    <button type="submit">
        Login
    </button>

</form>

</div>

</body>
</html>