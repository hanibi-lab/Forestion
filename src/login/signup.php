
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db_conn.php";

$User_Name     = $_POST['User_Name'];
$User_Id       = $_POST['User_Id'];
$User_Pwd      = md5($_POST['User_Pwd']); // md5 해시
$User_PhoneNum = $_POST['User_PhoneNum'];
$User_Addr     = $_POST['User_Addr'];
$User_SignDate = date("Y-m-d H:i:s"); // 가입일 현재 시간

// 중복 ID 체크
$check = mysqli_query($conn, "SELECT * FROM User_UR WHERE User_Id='$User_Id'");
if(mysqli_num_rows($check) > 0){
    die("Error: User ID already exists. <a href='signup_form.php'>Try again</a>");
}

$sql = "INSERT INTO User_UR(User_Name, User_Id, User_Pwd, User_PhoneNum, User_Addr, User_SignDate) 
        VALUES('$User_Name', '$User_Id', '$User_Pwd', '$User_PhoneNum', '$User_Addr', '$User_SignDate')";

$result = mysqli_query($conn, $sql);

if($result){
    echo "User registered successfully! <a href='index.php'>Login Now</a>";
}else{
    echo "Error: " . mysqli_error($conn);
}
?>
