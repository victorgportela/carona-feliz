<?php
require_once 'config.php';
require_once 'api_client.php';

$api = new ApiClient();
$user = $api->getLoggedUser();
$isDriver = $api->isDriver();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? SITE_TITLE ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-car me-2"></i>
                <?= SITE_TITLE ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>
                            Início
                        </a>
                    </li>
                    
                    <?php 
                    $userRole = $user['role'] ?? $user['user_type'] ?? 'passenger';
                    $canBeDriver = $isDriver || $userRole === 'both' || $userRole === 'driver';
                    $canBePassenger = !$isDriver || $userRole === 'both' || $userRole === 'passenger';
                    ?>
                    
                    <?php if ($canBeDriver): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="create_ride.php">
                                <i class="fas fa-plus-circle me-1"></i>
                                Criar Carona
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_rides.php">
                                <i class="fas fa-car me-1"></i>
                                Minhas Caronas
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($canBePassenger): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="search_rides.php">
                                <i class="fas fa-search me-1"></i>
                                Buscar Caronas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_requests.php">
                                <i class="fas fa-list me-1"></i>
                                Minhas Solicitações
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= htmlspecialchars($user['name'] ?? 'Usuário') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <span class="dropdown-item-text">
                                    <small class="text-muted">
                                        <?php 
                                        $userRole = $user['role'] ?? $user['user_type'] ?? 'passenger';
                                        switch($userRole) {
                                            case 'driver':
                                                echo 'Motorista';
                                                break;
                                            case 'passenger':
                                                echo 'Passageiro';
                                                break;
                                            case 'both':
                                                echo 'Motorista & Passageiro';
                                                break;
                                            default:
                                                echo 'Passageiro';
                                        }
                                        ?>
                                    </small>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="<?= isLoggedIn() ? 'py-4' : '' ?>">
        <?php
        // Exibir flash messages
        $flash = getFlash();
        if ($flash):
            $alertClass = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
        ?>
        <div class="container">
            <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html> 