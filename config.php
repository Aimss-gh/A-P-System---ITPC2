<?php

define('DB_HOST', 'sql105.infinityfree.com');
define('DB_USER', 'if0_40255781');
define('DB_PASS', 'clarabal2004');
define('DB_NAME', 'if0_40255781_APS');


try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
  
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}


session_start();

date_default_timezone_set('Asia/Manila');

function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}


function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}


function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}


function require_login() {
    if (!is_logged_in()) {
        header('Location: index.html');
        exit();
    }
}
?>