<?php
require_once 'includes/session.php';

// Если есть параметр page, обрабатываем
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    
    if ($page === 'dashboard') {
        require_once 'dashboard.php';
        exit;
    }
}

// По умолчанию - главная
require_once 'views/home.php';
?>