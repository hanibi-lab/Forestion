<html>
<head>
    <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 이미 로그인한 상태라면 home.php로 이동
if (isset($_SESSION['User_Id']) && isset($_SESSION['User_Name'])) {
    header("Location: home.php");
    exit();
}
?>
    
    <title>LOGIN</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="sign-body">
    <?php require "./header.php"; ?> 
    <!-- 헤더 --> 
    <!-- <form action="login.php" method="post"> -->
    <form action="login.php" method="post" class="sign-form">
        <h2>LOGIN</h2>
        <?php if (isset($_GET['error'])){?>
            <p class="error"><?php echo $_GET['error']; ?></p>
        <?php } ?>
        
        <label>User ID</label>
        <input type="text" name="User_Id" placeholder="User ID" required><br>

        <label>Password</label>
        <input type="password" name="User_Pwd" placeholder="Password" required><br>

        <button type="submit">Login</button>
        <a href="signup_form.php">Create an account</a>
    </form>
</body>
</html>
