<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Buscar Caronas - ' . SITE_TITLE;

requireLogin();

$api = new ApiClient();
$user = $api->getLoggedUser();

// Buscar caronas
$rides = [];
$hasSearched = false;
$filters = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $hasSearched = true;
    
    // Filtros
    if (!empty($_GET['origin'])) {
        $filters['origin'] = clean($_GET['origin']);
    }
    if (!empty($_GET['destination'])) {
        $filters['destination'] = clean($_GET['destination']);
    }
    if (!empty($_GET['date'])) {
        $filters['date'] = clean($_GET['date']);
    }
    if (!empty($_GET['max_price'])) {
        $filters['max_price'] = clean($_GET['max_price']);
    }
    
    $result = $api->getRides($filters);
    if ($result['success'] && isset($result['data']['data'])) {
        $rides = $result['data']['data'];
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4">
                <i class="fas fa-search text-green me-2"></i>
                Buscar Caronas
            </h4>
        </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtros de Busca
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="search_rides.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="origin" class="form-label">Origem</label>
                        <input type="text" class="form-control" id="origin" name="origin" 
                               value="<?= htmlspecialchars($_GET['origin'] ?? '') ?>"
                               placeholder="Ex: São Paulo, SP">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="destination" class="form-label">Destino</label>
                        <input type="text" class="form-control" id="destination" name="destination" 
                               value="<?= htmlspecialchars($_GET['destination'] ?? '') ?>"
                               placeholder="Ex: Rio de Janeiro, RJ">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Data</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?= htmlspecialchars($_GET['date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="max_price" class="form-label">Preço máximo</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" 
                               value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>"
                               min="0" step="0.01" placeholder="Ex: 50.00">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>
                        Buscar
                    </button>
                    <a href="search_rides.php" class="btn btn-outline-secondary">
                        <i class="fas fa-eraser me-2"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="row">
        <div class="col-12">
            <?php if (!$hasSearched): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">Use os filtros acima para buscar caronas</h5>
                    <p class="text-muted">Encontre a carona perfeita para sua viagem!</p>
                </div>
            <?php elseif (empty($rides)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search-minus text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">Nenhuma carona encontrada</h5>
                    <p class="text-muted">Tente ajustar os filtros de busca.</p>
                </div>
            <?php else: ?>
                <h6 class="mb-3">
                    <i class="fas fa-list me-2"></i>
                    <?= count($rides) ?> carona<?= count($rides) > 1 ? 's' : '' ?> encontrada<?= count($rides) > 1 ? 's' : '' ?>
                </h6>
                
                <?php foreach ($rides as $ride): ?>
                    <?php
                    $departureTime = new DateTime($ride['departure_time']);
                    $hasVehiclePhotos = !empty($ride['vehicle_photos']);
                    $firstPhotoUrl = $hasVehiclePhotos ? getImageUrl($ride['vehicle_photos'][0]['photo_path']) : '';
                    ?>
                    <div class="card ride-card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Motorista e Veículo -->
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
                                                 class="vehicle-image"
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
                                        <div class="col-sm-6">
                                            <div class="info-row">
                                                <i class="fas fa-users"></i>
                                                <?= $ride['available_seats'] ?> vaga<?= $ride['available_seats'] > 1 ? 's' : '' ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($ride['description'])): ?>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <?= htmlspecialchars(substr($ride['description'], 0, 100)) ?>
                                                <?= strlen($ride['description']) > 100 ? '...' : '' ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 d-flex align-items-center justify-content-end">
                                    <a href="ride_details.php?id=<?= $ride['id'] ?>" class="btn btn-primary w-100">
                                        <i class="fas fa-eye me-2"></i>
                                        Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 