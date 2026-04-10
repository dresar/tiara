<?php
// auth.php
session_start();

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
?>
