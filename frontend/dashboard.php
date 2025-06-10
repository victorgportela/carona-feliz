<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Dashboard - ' . SITE_TITLE;

requireLogin();

$api = new ApiClient();
$user = $api->getLoggedUser();
$isDriver = $api->isDriver();

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Cabeçalho de boas-vindas -->
            <div class="card mb-4 shadow-green">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-home me-2"></i>
                        Bem-vindo ao <?= SITE_TITLE ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5>Olá, <?= htmlspecialchars($user['name']) ?>!</h5>
                            <p class="mb-2">
                                <?php 
                                $userRole = $user['role'] ?? $user['user_type'] ?? 'passenger';
                                switch($userRole) {
                                    case 'driver':
                                        echo 'Você está logado como Motorista';
                                        break;
                                    case 'passenger':
                                        echo 'Você está logado como Passageiro';
                                        break;
                                    case 'both':
                                        echo 'Você pode ser Motorista e Passageiro';
                                        break;
                                    default:
                                        echo 'Você está logado como Passageiro';
                                }
                                ?>
                            </p>
                            <div>
                                <?php 
                                switch($userRole) {
                                    case 'driver':
                                        echo '<span class="badge bg-success">MOTORISTA</span>';
                                        break;
                                    case 'passenger':
                                        echo '<span class="badge bg-info">PASSAGEIRO</span>';
                                        break;
                                    case 'both':
                                        echo '<span class="badge bg-warning me-1">MOTORISTA</span>';
                                        echo '<span class="badge bg-info">PASSAGEIRO</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-info">PASSAGEIRO</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end">
                                <div class="bg-green rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-user text-white fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5 class="mb-4">O que você quer fazer?</h5>
        </div>
    </div>

    <div class="row">
        <?php 
        $userRole = $user['role'] ?? $user['user_type'] ?? 'passenger';
        $canBeDriver = $isDriver || $userRole === 'both' || $userRole === 'driver';
        $canBePassenger = !$isDriver || $userRole === 'both' || $userRole === 'passenger';
        ?>
        
        <?php if ($canBeDriver): ?>
            <!-- Funcionalidades para motorista -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 ride-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-green rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px;">
                                <i class="fas fa-plus-circle text-white fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Oferecer Carona</h5>
                                <p class="card-text text-muted mb-0">Crie uma nova oferta de carona</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <a href="create_ride.php" class="btn btn-primary w-100">
                                <i class="fas fa-plus-circle me-2"></i>
                                Criar Nova Carona
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 ride-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-green rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px;">
                                <i class="fas fa-car text-white fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Minhas Caronas</h5>
                                <p class="card-text text-muted mb-0">Gerencie suas ofertas de carona</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <a href="my_rides.php" class="btn btn-success w-100">
                                <i class="fas fa-car me-2"></i>
                                Ver Minhas Caronas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($canBePassenger): ?>
            <!-- Funcionalidades para passageiro -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 ride-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-green rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px;">
                                <i class="fas fa-search text-white fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Buscar Caronas</h5>
                                <p class="card-text text-muted mb-0">Encontre caronas disponíveis</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <a href="search_rides.php" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>
                                Buscar Caronas
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 ride-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-green rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px;">
                                <i class="fas fa-list text-white fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Minhas Solicitações</h5>
                                <p class="card-text text-muted mb-0">Acompanhe suas solicitações de carona</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <a href="my_requests.php" class="btn btn-warning w-100">
                                <i class="fas fa-list me-2"></i>
                                Ver Solicitações
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Seção de estatísticas rápidas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Resumo Rápido
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <i class="fas fa-car text-green fs-3"></i>
                                <h6 class="mt-2">Caronas</h6>
                                <p class="text-muted mb-0">
                                    <?= $isDriver ? 'Oferecidas por você' : 'Solicitadas por você' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <i class="fas fa-users text-green fs-3"></i>
                                <h6 class="mt-2">Conexões</h6>
                                <p class="text-muted mb-0">Pessoas que você conheceu</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <i class="fas fa-leaf text-green fs-3"></i>
                                <h6 class="mt-2">Sustentabilidade</h6>
                                <p class="text-muted mb-0">Contribuindo para o meio ambiente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 