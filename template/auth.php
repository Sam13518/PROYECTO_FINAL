<?php
session_start();
header('Content-Type: application/json');
include "conex.php";
$response = ["success" => false, "message" => "Invalid action"];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST["action"];
    // REGISTER 
    if ($action === "register") {
        $name       = trim($_POST["name"]);
        $email      = trim($_POST["email"]);
        $password   = $_POST["password"];
        $birth_date = $_POST["birth_date"];
        $address    = trim($_POST["address"]);
        $card       = trim($_POST["card_number"]);

        if (!empty($email) && !empty($password)) {
            $check = $conn->prepare("SELECT id_user FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $response = ["success"=>false, "message"=>"This email is already registered"];
            } else { $stmt = $conn->prepare("INSERT INTO users (name,email,password,birth_date,card_number,postal_address) 
                VALUES (?,?,?,?,?,?)");

                $stmt->bind_param("ssssss", $name, $email, $password, $birth_date, $card, $address);
                if($stmt->execute()){
                    $response = ["success"=>true, "message"=>"Account created successfully!"];
                } else { $response = ["success"=>false, "message"=>"Insert failed: ".$stmt->error];  }
                $stmt->close();
            } $check->close();  
        } else {  $response = ["success"=>false,"message"=>"Email and password are required"];
        } }

    // LOGIN
    if ($action === "login") {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        if(!empty($email) && !empty($password)){

            // CHECK ADMIN
            $stmtAdmin = $conn->prepare("SELECT id_admin,name,password FROM admins WHERE email=?");
            $stmtAdmin->bind_param("s", $email);
            $stmtAdmin->execute();
            $resultAdmin = $stmtAdmin->get_result();

            if($resultAdmin->num_rows === 1){
                $admin = $resultAdmin->fetch_assoc();
 if($password === trim($admin['password'])){
    $_SESSION["admin_id"] = $admin["id_admin"];
    $_SESSION["admin_name"] = $admin["name"];
    $response = ["success"=>true, "role"=>"admin"];
} else {
    $response = ["success"=>false, "message"=>"Incorrect password"];
} } else {
            //CHECK USER 
                $stmtUser = $conn->prepare("SELECT id_user,name,password FROM users WHERE email=?");
                $stmtUser->bind_param("s",$email);
                $stmtUser->execute();
                $resultUser = $stmtUser->get_result();

                if($resultUser->num_rows === 1){
                    $user = $resultUser->fetch_assoc();
                    if($password === $user['password']){
                        $_SESSION["user_id"] = $user["id_user"];
                        $_SESSION["user_name"] = $user["name"];
                        $_SESSION["email"]     = $email;
                        $response = ["success"=>true, "role"=>"user"];  // IMPORTANTE
                    } else { $response = ["success"=>false, "message"=>"Incorrect password"];
                    }  } else {   $response = ["success"=>false, "message"=>"Email not found"];    }
                $stmtUser->close();    }
            $stmtAdmin->close();
        } else {    $response = ["success"=>false, "message"=>"Please enter email and password"];
        } }
    //LOGOUT
    if ($action === "logout") {
        session_destroy();
        echo json_encode(["success" => true]);
        exit();
    } }
$conn->close();
echo json_encode($response);
?>
