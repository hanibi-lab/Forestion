<html>
<head>
    <title>SIGN UP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <form action="signup.php" method="post">
        <h2>SIGN UP</h2>
        
        <label>User Name</label>
        <input type="text" name="User_Name" placeholder="User Name" required><br>

        <label>User ID</label>
        <input type="text" name="User_Id" placeholder="User ID" required><br>

        <label>Password</label>
        <input type="password" name="User_Pwd" placeholder="Password" required><br>

        <label>Phone Number</label>
        <input type="text" name="User_PhoneNum" placeholder="Phone Number"><br>

        <label>Address</label>
        <input type="text" name="User_Addr" placeholder="Address"><br>

        <button type="submit">Sign Up</button>
        <a href="index.php">Back to Login</a>
    </form>
</body>
</html>
