<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';

$api = new ApiClient();

if ($api->isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?> 