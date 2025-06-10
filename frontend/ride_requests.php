<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Solicitações da Carona - ' . SITE_TITLE;

requireLogin();

$api = new ApiClient();
$user = $api->getLoggedUser();

$rideId = intval($_GET['id'] ?? 0);
if ($rideId <= 0) {
    setFlash('error', 'Carona não encontrada.');
    redirect('my_rides.php');
}

// Buscar detalhes da carona e suas solicitações
$rideResult = $api->getRideDetails($rideId);
if (!$rideResult['success']) {
    setFlash('error', $rideResult['message'] ?? 'Carona não encontrada.');
    redirect('my_rides.php');
}

$ride = $rideResult['data'];

// Verificar se é o proprietário da carona
if ($ride['driver_id'] != $user['id']) {
    setFlash('error', 'Você não tem permissão para acessar esta página.');
    redirect('my_rides.php');
}

// Buscar solicitações da carona
$requestsResult = $api->getRideRequests($rideId);
$requests = [];
if ($requestsResult['success'] && isset($requestsResult['data']['data'])) {
    $requests = $requestsResult['data']['data'];
}

// Aprovar/Rejeitar solicitação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $requestId = intval($_POST['request_id'] ?? 0);
    
    if ($requestId > 0 && in_array($action, ['approve', 'reject'])) {
        if ($action === 'approve') {
            $result = $api->approveRequest($requestId);
            $message = 'Solicitação aprovada com sucesso!';
        } else {
            $result = $api->rejectRequest($requestId);
            $message = 'Solicitação rejeitada.';
        }
        
        if ($result['success']) {
            setFlash('success', $message);
        } else {
            setFlash('error', $result['message'] ?? 'Erro ao processar solicitação.');
        }
        
        redirect("ride_requests.php?id=$rideId");
    }
}

$departureTime = new DateTime($ride['departure_time']);
$now = new DateTime();
$isUpcoming = $departureTime > $now;

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="fas fa-users text-green me-2"></i>
                    Solicitações da Carona
                </h4>
                <a href="my_rides.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Informações da Carona -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-car me-2"></i>
                Detalhes da Carona
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="location location-origin mb-2">
                        <i class="fas fa-map-marker-alt"></i>
                        <strong>Origem:</strong> <?= htmlspecialchars($ride['origin']) ?>
                    </div>
                    <div class="location location-destination mb-3">
                        <i class="fas fa-flag-checkered"></i>
                        <strong>Destino:</strong> <?= htmlspecialchars($ride['destination']) ?>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="info-row">
                                <i class="fas fa-calendar"></i>
                                <?= formatDate($ride['departure_time']) ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-row">
                                <i class="fas fa-clock"></i>
                                <?= formatTime($ride['departure_time']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="price fs-4 mb-2"><?= formatPrice($ride['price']) ?></div>
                    <span class="badge <?= getStatusBadgeClass($ride['status']) ?>">
                        <?= getStatusText($ride['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Solicitações -->
    <?php if (empty($requests)): ?>
        <div class="text-center py-5">
            <i class="fas fa-users text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">Nenhuma solicitação encontrada</h5>
            <p class="text-muted">Ainda não há solicitações para esta carona.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <h6 class="mb-3">
                    <i class="fas fa-list me-2"></i>
                    <?= count($requests) ?> solicitaç<?= count($requests) > 1 ? 'ões' : 'ão' ?> encontrada<?= count($requests) > 1 ? 's' : '' ?>
                </h6>
            </div>
        </div>

        <div class="row">
            <?php foreach ($requests as $request): ?>
                <?php
                $passenger = $request['passenger'];
                $requestDate = new DateTime($request['created_at']);
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="card request-card h-100">
                        <div class="card-body">
                            <!-- Status -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge <?= getRequestStatusBadgeClass($request['status']) ?>">
                                    <?= getRequestStatusText($request['status']) ?>
                                </span>
                                <small class="text-muted">
                                    <?= formatDate($request['created_at']) ?>
                                </small>
                            </div>

                            <!-- Passageiro -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-green rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px;">
                                    <span class="text-white fw-bold">
                                        <?= strtoupper(substr($passenger['name'], 0, 1)) ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($passenger['name']) ?></h6>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?= htmlspecialchars($passenger['email']) ?>
                                    </p>
                                    <?php if (!empty($passenger['phone'])): ?>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-phone me-1"></i>
                                            <?= htmlspecialchars($passenger['phone']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Informações da Solicitação -->
                            <div class="request-info mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-plus text-muted me-2"></i>
                                    <small class="text-muted">
                                        Solicitação feita em <?= formatDateTime($request['created_at']) ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Ações -->
                            <?php if ($request['status'] === 'pending' && $isUpcoming && $ride['status'] === 'active'): ?>
                                <div class="d-flex gap-2">
                                    <form method="POST" class="flex-fill">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <button type="submit" class="btn btn-success w-100" 
                                                onclick="return confirm('Confirma a aprovação desta solicitação?')">
                                            <i class="fas fa-check me-2"></i>
                                            Aprovar
                                        </button>
                                    </form>
                                    <form method="POST" class="flex-fill">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <button type="submit" class="btn btn-danger w-100" 
                                                onclick="return confirm('Confirma a rejeição desta solicitação?')">
                                            <i class="fas fa-times me-2"></i>
                                            Rejeitar
                                        </button>
                                    </form>
                                </div>
                            <?php elseif ($request['status'] === 'approved'): ?>
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Solicitação aprovada</strong><br>
                                    Entre em contato com o passageiro para combinar os detalhes.
                                    <?php if (!empty($passenger['phone'])): ?>
                                        <div class="mt-2">
                                            <a href="tel:<?= htmlspecialchars($passenger['phone']) ?>" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-phone me-1"></i>
                                                Ligar
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($request['status'] === 'rejected'): ?>
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>Solicitação rejeitada</strong>
                                </div>
                            <?php elseif ($request['status'] === 'cancelled'): ?>
                                <div class="alert alert-secondary mb-0">
                                    <i class="fas fa-ban me-2"></i>
                                    <strong>Solicitação cancelada pelo passageiro</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 