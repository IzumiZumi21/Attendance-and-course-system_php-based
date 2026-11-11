<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if (empty($_SESSION['admin_id'])) {
    redirect('/auth/login.php');
}