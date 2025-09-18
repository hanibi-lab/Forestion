<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db_conn.php";

if(isset($_POST['User_Id']) && isset($_POST['User_Pwd'])) {

    function validate($data){
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $User_Id  = validate($_POST['User_Id']);
    $User_Pwd = md5(validate($_POST['User_Pwd'])); // md5 해시

    if(empty($User_Id)){
        header("Location: index.php?error=User ID is required");
        exit();
    } elseif(empty($User_Pwd)){
        header("Location: index.php?error=Password is required");
        exit();
    }

    $sql = "SELECT * FROM User_UR WHERE User_Id='$User_Id' AND User_Pwd='$User_Pwd'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) === 1){
        $row = mysqli_fetch_assoc($result);
        $_SESSION['User_Name'] = $row['User_Name'];
        $_SESSION['User_Id']   = $row['User_Id'];
        header("Location: home.php");
        exit();
    } else {
        header("Location: index.php?error=Incorrect User ID or Password");
        exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>