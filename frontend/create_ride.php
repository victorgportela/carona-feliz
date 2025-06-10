<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Criar Carona - ' . SITE_TITLE;

requireLogin();

$api = new ApiClient();
$user = $api->getLoggedUser();

// Verificar se o usuário pode ser motorista
if (!in_array($user['user_type'], ['driver', 'both'])) {
    setFlash('error', 'Você precisa ser motorista para criar caronas.');
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validações
    $origin = clean($_POST['origin'] ?? '');
    $destination = clean($_POST['destination'] ?? '');
    $departure_time = clean($_POST['departure_time'] ?? '');
    $price = clean($_POST['price'] ?? '');
    $available_seats = clean($_POST['available_seats'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $vehicle_model = clean($_POST['vehicle_model'] ?? '');
    $vehicle_color = clean($_POST['vehicle_color'] ?? '');
    $vehicle_plate = clean($_POST['vehicle_plate'] ?? '');
    
    if (empty($origin)) {
        $errors[] = 'O campo origem é obrigatório.';
    }
    
    if (empty($destination)) {
        $errors[] = 'O campo destino é obrigatório.';
    }
    
    if (empty($departure_time)) {
        $errors[] = 'A data e hora de partida são obrigatórias.';
    } else {
        $departureDateTime = new DateTime($departure_time);
        $now = new DateTime();
        if ($departureDateTime <= $now) {
            $errors[] = 'A data e hora de partida devem ser futuras.';
        }
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = 'O preço deve ser um valor válido maior que zero.';
    }
    
    if (empty($available_seats) || !is_numeric($available_seats) || $available_seats <= 0 || $available_seats > 10) {
        $errors[] = 'O número de vagas deve ser entre 1 e 10.';
    }
    
    if (empty($vehicle_model)) {
        $errors[] = 'O modelo do veículo é obrigatório.';
    }
    
    if (empty($vehicle_color)) {
        $errors[] = 'A cor do veículo é obrigatória.';
    }
    
    if (empty($vehicle_plate)) {
        $errors[] = 'A placa do veículo é obrigatória.';
    }
    
    if (empty($errors)) {
        $rideData = [
            'origin' => $origin,
            'destination' => $destination,
            'departure_time' => $departure_time,
            'price' => floatval($price),
            'available_seats' => intval($available_seats),
            'description' => $description,
            'vehicle_model' => $vehicle_model,
            'vehicle_color' => $vehicle_color,
            'vehicle_plate' => $vehicle_plate
        ];
        
        $result = $api->createRide($rideData);
        
        if ($result['success']) {
            setFlash('success', 'Carona criada com sucesso!');
            redirect('my_rides.php');
        } else {
            $errors[] = $result['message'] ?? 'Erro ao criar carona.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <h4 class="mb-4">
                <i class="fas fa-plus-circle text-green me-2"></i>
                Criar Nova Carona
            </h4>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="create_ride.php" id="createRideForm">
                <!-- Informações da Viagem -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-route me-2"></i>
                            Informações da Viagem
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="origin" class="form-label">
                                    Origem <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="origin" name="origin" 
                                       value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>"
                                       placeholder="Ex: São Paulo, SP" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="destination" class="form-label">
                                    Destino <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="destination" name="destination" 
                                       value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>"
                                       placeholder="Ex: Rio de Janeiro, RJ" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departure_time" class="form-label">
                                    Data e Hora de Partida <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" 
                                       value="<?= htmlspecialchars($_POST['departure_time'] ?? '') ?>"
                                       min="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">
                                    Preço por Pessoa (R$) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                                       min="0" step="0.01" placeholder="Ex: 50.00" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="available_seats" class="form-label">
                                    Vagas Disponíveis <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="available_seats" name="available_seats" required>
                                    <option value="">Selecione...</option>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($_POST['available_seats'] ?? '') == $i ? 'selected' : '' ?>>
                                            <?= $i ?> vaga<?= $i > 1 ? 's' : '' ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Observações</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Informações adicionais sobre a viagem (opcional)"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Informações do Veículo -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-car me-2"></i>
                            Informações do Veículo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_model" class="form-label">
                                    Modelo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="vehicle_model" name="vehicle_model" 
                                       value="<?= htmlspecialchars($_POST['vehicle_model'] ?? '') ?>"
                                       placeholder="Ex: Honda Civic" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_color" class="form-label">
                                    Cor <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="vehicle_color" name="vehicle_color" 
                                       value="<?= htmlspecialchars($_POST['vehicle_color'] ?? '') ?>"
                                       placeholder="Ex: Branco" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_plate" class="form-label">
                                    Placa <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="vehicle_plate" name="vehicle_plate" 
                                       value="<?= htmlspecialchars($_POST['vehicle_plate'] ?? '') ?>"
                                       placeholder="Ex: ABC-1234" required 
                                       pattern="[A-Z]{3}-[0-9]{4}" maxlength="8">
                                <div class="form-text">Formato: ABC-1234</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botões -->
                <div class="d-flex justify-content-between mb-4">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Criar Carona
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatação da placa
    const plateInput = document.getElementById('vehicle_plate');
    if (plateInput) {
        plateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7);
            }
            e.target.value = value;
        });
    }
    
    // Validação do formulário
    const form = document.getElementById('createRideForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const departureTime = new Date(document.getElementById('departure_time').value);
            const now = new Date();
            
            if (departureTime <= now) {
                e.preventDefault();
                alert('A data e hora de partida devem ser futuras.');
                return false;
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 