<html>
<head>
    
    <title>LOGIN</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <form action="login.php" method="post">
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