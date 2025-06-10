<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Minhas Caronas - ' . SITE_TITLE;

requireLogin();

$api = new ApiClient();
$user = $api->getLoggedUser();

// Verificar se o usuário pode ser motorista
if (!in_array($user['user_type'], ['driver', 'both'])) {
    setFlash('error', 'Você precisa ser motorista para acessar esta página.');
    redirect('dashboard.php');
}

// Buscar as caronas do motorista
$result = $api->getMyRides();
$rides = [];
if ($result['success'] && isset($result['data']['data'])) {
    $rides = $result['data']['data'];
}

// Cancelar carona
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $rideId = intval($_POST['ride_id'] ?? 0);
    if ($rideId > 0) {
        $result = $api->cancelRide($rideId);
        if ($result['success']) {
            setFlash('success', 'Carona cancelada com sucesso!');
        } else {
            setFlash('error', $result['message'] ?? 'Erro ao cancelar carona.');
        }
        redirect('my_rides.php');
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="fas fa-car text-green me-2"></i>
                    Minhas Caronas
                </h4>
                <a href="create_ride.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Nova Carona
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($rides)): ?>
        <div class="text-center py-5">
            <i class="fas fa-car text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">Nenhuma carona encontrada</h5>
            <p class="text-muted mb-4">Você ainda não criou nenhuma carona.</p>
            <a href="create_ride.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Criar Primeira Carona
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($rides as $ride): ?>
                <?php
                $departureTime = new DateTime($ride['departure_time']);
                $now = new DateTime();
                $isUpcoming = $departureTime > $now;
                $hasVehiclePhotos = !empty($ride['vehicle_photos']);
                $firstPhotoUrl = $hasVehiclePhotos ? getImageUrl($ride['vehicle_photos'][0]['photo_path']) : '';
                $requestCount = isset($ride['requests_count']) ? $ride['requests_count'] : 0;
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="card ride-card h-100">
                        <div class="card-body">
                            <!-- Status e Ações -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge <?= getStatusBadgeClass($ride['status']) ?>">
                                    <?= getStatusText($ride['status']) ?>
                                </span>
                                <?php if ($isUpcoming && $ride['status'] === 'active'): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="ride_requests.php?id=<?= $ride['id'] ?>">
                                                    <i class="fas fa-users me-2"></i>
                                                    Gerenciar Solicitações
                                                </a>
                                            </li>
                                            <li>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Tem certeza que deseja cancelar esta carona?')">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-times me-2"></i>
                                                        Cancelar Carona
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Imagem do Veículo -->
                            <?php if ($hasVehiclePhotos && $firstPhotoUrl): ?>
                                <div class="mb-3">
                                    <img src="<?= $firstPhotoUrl ?>" 
                                         alt="Foto do veículo" 
                                         class="vehicle-image-large"
                                         data-fallback="true">
                                    <?php if (count($ride['vehicle_photos']) > 1): ?>
                                        <small class="text-muted d-block text-center mt-1">
                                            +<?= count($ride['vehicle_photos']) - 1 ?> foto<?= count($ride['vehicle_photos']) > 2 ? 's' : '' ?> adicional<?= count($ride['vehicle_photos']) > 2 ? 'is' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Informações do Veículo -->
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-car text-muted me-2"></i>
                                <span class="fw-bold me-2"><?= htmlspecialchars($ride['vehicle_model']) ?></span>
                                <span class="text-muted me-2">•</span>
                                <span class="text-muted"><?= htmlspecialchars($ride['vehicle_color']) ?></span>
                            </div>

                            <!-- Rota -->
                            <div class="location location-origin mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <strong>Origem:</strong> <?= htmlspecialchars($ride['origin']) ?>
                            </div>
                            <div class="location location-destination mb-3">
                                <i class="fas fa-flag-checkered"></i>
                                <strong>Destino:</strong> <?= htmlspecialchars($ride['destination']) ?>
                            </div>

                            <!-- Informações da Viagem -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="info-row">
                                        <i class="fas fa-calendar"></i>
                                        <?= formatDate($ride['departure_time']) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-row">
                                        <i class="fas fa-clock"></i>
                                        <?= formatTime($ride['departure_time']) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-row">
                                        <i class="fas fa-users"></i>
                                        <?= $ride['available_seats'] ?> vaga<?= $ride['available_seats'] > 1 ? 's' : '' ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-row price">
                                        <i class="fas fa-tag"></i>
                                        <?= formatPrice($ride['price']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Solicitações -->
                            <?php if ($requestCount > 0): ?>
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-users me-2"></i>
                                    <strong><?= $requestCount ?></strong> 
                                    solicitaç<?= $requestCount > 1 ? 'ões' : 'ão' ?> pendente<?= $requestCount > 1 ? 's' : '' ?>
                                </div>
                            <?php endif; ?>

                            <!-- Descrição -->
                            <?php if (!empty($ride['description'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?= htmlspecialchars($ride['description']) ?>
                                    </small>
                                </div>
                            <?php endif; ?>

                            <!-- Ações -->
                            <div class="d-flex gap-2">
                                <a href="ride_details.php?id=<?= $ride['id'] ?>" class="btn btn-outline-primary flex-fill">
                                    <i class="fas fa-eye me-2"></i>
                                    Detalhes
                                </a>
                                <?php if ($requestCount > 0 && $isUpcoming && $ride['status'] === 'active'): ?>
                                    <a href="ride_requests.php?id=<?= $ride['id'] ?>" class="btn btn-primary flex-fill">
                                        <i class="fas fa-users me-2"></i>
                                        Solicitações
                                        <span class="badge bg-white text-green ms-1"><?= $requestCount ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 