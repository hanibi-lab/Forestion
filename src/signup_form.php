<html>
<head>
    <title>SIGN UP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="sign-body">
    <?php require "./header.php"; ?> 
    <!-- 헤더 --> 
    <!-- <form action="signup.php" method="post"> -->
    <form action="signup.php" method="post" class="sign-form">
        <h2>회원가입/h2>
        
        <label>사용자 이름</label>
        <input type="text" name="User_Name" placeholder="User Name" required><br>

        <label>아이디</label>
        <input type="text" name="User_Id" placeholder="User ID" required><br>

        <label>비밀번호</label>
        <input type="password" name="User_Pwd" placeholder="Password" required><br>

        <label>전화번호</label>
        <input type="text" name="User_PhoneNum" maxlength="15" inputmode="numeric" pattern="\d*"><br>

        <label>주소</label>
        <input type="text" name="User_Addr" placeholder="Address"><br>

        <button type="submit">가입하기</button>
        <a href="index.php">로그인 하러가기</a>
    </form>
</body>
</html>
