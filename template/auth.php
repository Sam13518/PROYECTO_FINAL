<?php
session_start();
header('Content-Type: application/json');
include "conex.php";

$response = ["success" => false, "message" => "Invalid action"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST["action"];

    if ($action === "register") {
        $name       = trim($_POST["name"]);
        $email      = trim($_POST["email"]);
        $password   = $_POST["password"];
        $birth_date = $_POST["birth_date"];
        $address    = trim($_POST["address"]);
        $card       = trim($_POST["card_number"]);

        if (!empty($email) && !empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $check = $conn->prepare("SELECT id FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $response = ["success"=>false, "message"=>"This email is already registered"];
            } else {
                $stmt = $conn->prepare("INSERT INTO users (name,email,password,birth_date,card_number,postal_address) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("ssssss", $name,$email,$hashed_password,$birth_date,$card,$address);

                if($stmt->execute()){
                    $response = ["success"=>true, "message"=>"Account created successfully!"];
                } else {
                    $response = ["success"=>false, "message"=>"Insert failed: ".$stmt->error];
                }
                $stmt->close();
            }
            $check->close();
        } else {
            $response = ["success"=>false,"message"=>"Email and password are required"];
        }
    }

    if ($action === "login") {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        if(!empty($email) && !empty($password)){
            $stmt = $conn->prepare("SELECT id,name,password FROM users WHERE email=?");
            $stmt->bind_param("s",$email);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows===1){
                $user = $result->fetch_assoc();
                if(password_verify($password,$user['password'])){
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["email"] = $email;
                    $response=["success"=>true,"message"=>"Login successful!"];
                } else {
                    $response=["success"=>false,"message"=>"Incorrect password"];
                }
            } else {
                $response=["success"=>false,"message"=>"Email not found"];
            }
            $stmt->close();
        } else {
            $response=["success"=>false,"message"=>"Please enter email and password"];
        }
    }
}

$conn->close();
echo json_encode($response);
?>
