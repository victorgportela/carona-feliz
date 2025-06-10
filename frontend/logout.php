<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';

$api = new ApiClient();

if ($api->isLoggedIn()) {
    $result = $api->logout();
    
    if ($result['success']) {
        setFlash('success', 'Logout realizado com sucesso!');
    } else {
        setFlash('error', 'Erro ao fazer logout.');
    }
}

redirect('login.php');
?> 