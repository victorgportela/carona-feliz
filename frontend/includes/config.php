<?php
session_start();

// Configurações gerais
define('SITE_TITLE', 'Carona Feliz');
define('BASE_URL', 'http://localhost:8080'); // Ajuste conforme necessário
define('STORAGE_URL', 'http://localhost:8000/storage');

// Funções auxiliares
function redirect($url) {
    header("Location: $url");
    exit;
}

function asset($path) {
    return BASE_URL . '/assets/' . $path;
}

function formatDate($date) {
    if (empty($date)) return '';
    
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('d/m/Y');
    } catch (Exception $e) {
        return $date;
    }
}

function formatTime($date) {
    if (empty($date)) return '';
    
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('H:i');
    } catch (Exception $e) {
        return $date;
    }
}

function formatDateTime($date) {
    if (empty($date)) return '';
    
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $date;
    }
}

function formatPrice($price) {
    return 'R$ ' . number_format(floatval($price), 2, ',', '.');
}

function getImageUrl($path) {
    if (empty($path)) return '';
    return "http://localhost:8000/storage-image/" . ltrim($path, '/');
}



function getStatusColor($status) {
    $colors = [
        'active' => 'success',
        'completed' => 'info',
        'cancelled' => 'danger',
        'pending' => 'warning',
        'accepted' => 'success',
        'rejected' => 'danger'
    ];
    
    return $colors[$status] ?? 'secondary';
}

function requireGuest() {
    if (isLoggedIn()) {
        redirect('dashboard.php');
    }
}

// Sanitização de dados
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validação de email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Funções para flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function hasFlash() {
    return isset($_SESSION['flash']);
}

// Verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['token']) && !empty($_SESSION['token']);
}

// Verificar login obrigatório
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Você precisa estar logado para acessar esta página.');
        redirect('login.php');
    }
}

// Logout
function logout() {
    session_destroy();
    redirect('login.php');
}

// Classe CSS para status da carona
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'active':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        case 'completed':
            return 'bg-secondary';
        default:
            return 'bg-warning';
    }
}

// Texto do status da carona
function getStatusText($status) {
    switch ($status) {
        case 'active':
            return 'Ativa';
        case 'cancelled':
            return 'Cancelada';
        case 'completed':
            return 'Concluída';
        default:
            return 'Pendente';
    }
}

// Classe CSS para status da solicitação
function getRequestStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning';
        case 'approved':
            return 'bg-success';
        case 'rejected':
            return 'bg-danger';
        case 'cancelled':
            return 'bg-secondary';
        default:
            return 'bg-secondary';
    }
}

// Texto do status da solicitação
function getRequestStatusText($status) {
    switch ($status) {
        case 'pending':
            return 'Pendente';
        case 'approved':
            return 'Aprovada';
        case 'rejected':
            return 'Rejeitada';
        case 'cancelled':
            return 'Cancelada';
        default:
            return 'Desconhecido';
    }
}

// Validação de email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validação de senha
function validatePassword($password) {
    return strlen($password) >= 6;
}

// Validação de telefone
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 11;
} 