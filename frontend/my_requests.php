<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Minhas Solicitações - ' . SITE_TITLE;

requireLogin();

$api = new ApiClient();
$user = $api->getLoggedUser();

// Verificar se o usuário pode ser passageiro
if (!in_array($user['user_type'], ['passenger', 'both'])) {
    setFlash('error', 'Você precisa ser passageiro para acessar esta página.');
    redirect('dashboard.php');
}

// Buscar as solicitações do passageiro
$result = $api->getMyRequests();
$requests = [];
if ($result['success'] && isset($result['data']['data'])) {
    $requests = $result['data']['data'];
}

// Cancelar solicitação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $requestId = intval($_POST['request_id'] ?? 0);
    if ($requestId > 0) {
        $result = $api->cancelRequest($requestId);
        if ($result['success']) {
            setFlash('success', 'Solicitação cancelada com sucesso!');
        } else {
            setFlash('error', $result['message'] ?? 'Erro ao cancelar solicitação.');
        }
        redirect('my_requests.php');
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="fas fa-paper-plane text-green me-2"></i>
                    Minhas Solicitações
                </h4>
                <a href="search_rides.php" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Buscar Caronas
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($requests)): ?>
        <div class="text-center py-5">
            <i class="fas fa-paper-plane text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">Nenhuma solicitação encontrada</h5>
            <p class="text-muted mb-4">Você ainda não fez nenhuma solicitação de carona.</p>
            <a href="search_rides.php" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>
                Buscar Caronas
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($requests as $request): ?>
                <?php
                $ride = $request['ride'];
                $departureTime = new DateTime($ride['departure_time']);
                $now = new DateTime();
                $isUpcoming = $departureTime > $now;
                $hasVehiclePhotos = !empty($ride['vehicle_photos']);
                $firstPhotoUrl = $hasVehiclePhotos ? getImageUrl($ride['vehicle_photos'][0]['photo_path']) : '';
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="card request-card h-100">
                        <div class="card-body">
                            <!-- Status e Ações -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge <?= getRequestStatusBadgeClass($request['status']) ?>">
                                    <?= getRequestStatusText($request['status']) ?>
                                </span>
                                <?php if ($isUpcoming && $request['status'] === 'pending'): ?>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Tem certeza que deseja cancelar esta solicitação?')">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times me-1"></i>
                                            Cancelar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <!-- Motorista -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-green rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px;">
                                    <span class="text-white fw-bold">
                                        <?= strtoupper(substr($ride['driver']['name'], 0, 1)) ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($ride['driver']['name']) ?></h6>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-car text-muted me-1" style="font-size: 0.8rem;"></i>
                                        <small class="text-muted me-2"><?= htmlspecialchars($ride['vehicle_model']) ?></small>
                                        <?php if ($hasVehiclePhotos): ?>
                                            <span class="photo-badge">
                                                <i class="fas fa-camera"></i>
                                                <?= count($ride['vehicle_photos']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="price">
                                    <?= formatPrice($ride['price']) ?>
                                </div>
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
                                    <div class="info-row">
                                        <i class="fas fa-calendar-plus"></i>
                                        <?= formatDate($request['created_at']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Informações da Solicitação -->
                            <?php if ($request['status'] === 'approved'): ?>
                                <div class="alert alert-success mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Solicitação Aprovada!</strong><br>
                                    Entre em contato com o motorista para combinar os detalhes.
                                </div>
                            <?php elseif ($request['status'] === 'rejected'): ?>
                                <div class="alert alert-danger mb-3">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>Solicitação Rejeitada</strong><br>
                                    O motorista não aprovou sua solicitação.
                                </div>
                            <?php elseif ($request['status'] === 'pending'): ?>
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Aguardando Aprovação</strong><br>
                                    O motorista ainda não respondeu à sua solicitação.
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
                                    Ver Detalhes
                                </a>
                                <?php if ($request['status'] === 'approved'): ?>
                                    <a href="tel:<?= htmlspecialchars($ride['driver']['phone'] ?? '') ?>" 
                                       class="btn btn-success flex-fill"
                                       <?= empty($ride['driver']['phone']) ? 'disabled' : '' ?>>
                                        <i class="fas fa-phone me-2"></i>
                                        Contato
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