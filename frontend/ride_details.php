<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Detalhes da Carona - ' . SITE_TITLE;

requireLogin();

$api = new ApiClient();
$user = $api->getLoggedUser();

$rideId = intval($_GET['id'] ?? 0);
if ($rideId <= 0) {
    setFlash('error', 'Carona não encontrada.');
    redirect('dashboard.php');
}

// Buscar detalhes da carona
$result = $api->getRideDetails($rideId);
if (!$result['success']) {
    setFlash('error', $result['message'] ?? 'Carona não encontrada.');
    redirect('dashboard.php');
}

$ride = $result['data'];
$isOwner = $ride['driver_id'] == $user['id'];
$canRequestRide = !$isOwner && in_array($user['user_type'], ['passenger', 'both']) && $ride['status'] === 'active';

$departureTime = new DateTime($ride['departure_time']);
$now = new DateTime();
$isUpcoming = $departureTime > $now;
$hasVehiclePhotos = !empty($ride['vehicle_photos']);

// Solicitar carona
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_ride') {
    if (!$canRequestRide) {
        setFlash('error', 'Você não pode solicitar esta carona.');
    } else {
        $result = $api->requestRide($rideId);
        if ($result['success']) {
            setFlash('success', 'Solicitação enviada com sucesso! Aguarde a aprovação do motorista.');
        } else {
            setFlash('error', $result['message'] ?? 'Erro ao enviar solicitação.');
        }
    }
    redirect("ride_details.php?id=$rideId");
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="fas fa-car text-green me-2"></i>
                    Detalhes da Carona
                </h4>
                <span class="badge <?= getStatusBadgeClass($ride['status']) ?> fs-6">
                    <?= getStatusText($ride['status']) ?>
                </span>
            </div>

            <!-- Motorista -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Motorista
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-green rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px;">
                            <span class="text-white fw-bold fs-4">
                                <?= strtoupper(substr($ride['driver']['name'], 0, 1)) ?>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1"><?= htmlspecialchars($ride['driver']['name']) ?></h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-envelope me-2"></i>
                                <?= htmlspecialchars($ride['driver']['email']) ?>
                            </p>
                            <?php if (!empty($ride['driver']['phone'])): ?>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-phone me-2"></i>
                                    <?= htmlspecialchars($ride['driver']['phone']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Veículo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-car me-2"></i>
                        Veículo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="vehicle-info">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-car text-muted me-2"></i>
                                    <strong class="me-2">Modelo:</strong>
                                    <span><?= htmlspecialchars($ride['vehicle_model']) ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-palette text-muted me-2"></i>
                                    <strong class="me-2">Cor:</strong>
                                    <span><?= htmlspecialchars($ride['vehicle_color']) ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-id-card text-muted me-2"></i>
                                    <strong class="me-2">Placa:</strong>
                                    <span><?= htmlspecialchars($ride['vehicle_plate']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php if ($hasVehiclePhotos): ?>
                                <div class="photo-count text-center">
                                    <i class="fas fa-camera text-green" style="font-size: 2rem;"></i>
                                    <div class="fw-bold"><?= count($ride['vehicle_photos']) ?></div>
                                    <small class="text-muted">foto<?= count($ride['vehicle_photos']) > 1 ? 's' : '' ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Fotos do Veículo -->
                    <?php if ($hasVehiclePhotos): ?>
                        <hr>
                        <div class="vehicle-photos">
                            <div class="row g-2">
                                <?php foreach ($ride['vehicle_photos'] as $photo): ?>
                                    <div class="col-md-4 col-6">
                                        <img src="<?= getImageUrl($photo['photo_path']) ?>" 
                                             alt="Foto do veículo" 
                                             class="img-fluid rounded cursor-pointer vehicle-photo"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#photoModal"
                                             data-photo-url="<?= getImageUrl($photo['photo_path']) ?>"
                                             data-fallback="true">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informações da Viagem -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-route me-2"></i>
                        Detalhes da Viagem
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Rota -->
                    <div class="location location-origin mb-3">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Origem</strong>
                            <div><?= htmlspecialchars($ride['origin']) ?></div>
                        </div>
                    </div>
                    <div class="location location-destination mb-4">
                        <i class="fas fa-flag-checkered"></i>
                        <div>
                            <strong>Destino</strong>
                            <div><?= htmlspecialchars($ride['destination']) ?></div>
                        </div>
                    </div>

                    <!-- Informações -->
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-calendar text-green me-2"></i>
                                <strong>Data:</strong> <?= formatDate($ride['departure_time']) ?>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-clock text-green me-2"></i>
                                <strong>Horário:</strong> <?= formatTime($ride['departure_time']) ?>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-users text-green me-2"></i>
                                <strong>Vagas:</strong> <?= $ride['available_seats'] ?> disponível<?= $ride['available_seats'] > 1 ? 'is' : '' ?>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-tag text-green me-2"></i>
                                <strong>Preço:</strong> 
                                <span class="price fs-5"><?= formatPrice($ride['price']) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($ride['description'])): ?>
                        <hr>
                        <div class="ride-description">
                            <h6>
                                <i class="fas fa-info-circle text-green me-2"></i>
                                Observações
                            </h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($ride['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ações -->
            <div class="d-flex gap-3 mb-4">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Voltar
                </a>
                
                <?php if ($canRequestRide && $isUpcoming): ?>
                    <form method="POST" class="flex-fill">
                        <input type="hidden" name="action" value="request_ride">
                        <button type="submit" class="btn btn-primary w-100" 
                                onclick="return confirm('Confirma a solicitação desta carona?')">
                            <i class="fas fa-paper-plane me-2"></i>
                            Solicitar Carona
                        </button>
                    </form>
                <?php elseif ($isOwner): ?>
                    <a href="ride_requests.php?id=<?= $ride['id'] ?>" class="btn btn-primary flex-fill">
                        <i class="fas fa-users me-2"></i>
                        Gerenciar Solicitações
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto do Veículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalPhoto" src="" alt="Foto do veículo" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal de fotos
    const photoModal = document.getElementById('photoModal');
    const modalPhoto = document.getElementById('modalPhoto');
    
    if (photoModal && modalPhoto) {
        photoModal.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            const photoUrl = trigger.getAttribute('data-photo-url');
            modalPhoto.src = photoUrl;
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 