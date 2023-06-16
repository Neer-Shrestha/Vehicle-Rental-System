<?php
require_once 'backend/database/db_config.php';
require_once 'users/index.php';


function validate_user(string $name, string $username, string $password){
    global $conn;

    $message = [];
    if(empty($name)){
        $message['name'] = 'Name is empty';
    }
    if(empty($username)){
        $message['username'] = 'Username is empty';
    }else{
        $stmt=$conn->prepare('SELECT * FROM re_users WHERE user_login=?');
        $stmt->bind_param('s', $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $users = $result->fetch_array(MYSQLI_ASSOC);

        if(isset($users)){
            $message['username'] = "Username Already Exists";
        }
    }
    if(empty($email)){
        $message['email'] = 'Email is empty';
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message['email'] = "Invalid Email Format";
    }
    if(empty($password)){
        $message['password'] = "Password is empty";
    }
    return $message;
}
