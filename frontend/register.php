<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Cadastro - ' . SITE_TITLE;

$api = new ApiClient();

// Redirecionar se já estiver logado
if ($api->isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userType = $_POST['user_type'] ?? '';
    
    // Validações
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword) || empty($userType)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (!isValidEmail($email)) {
        $error = 'Por favor, insira um email válido.';
    } elseif ($password !== $confirmPassword) {
        $error = 'As senhas não coincidem.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $userData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'password_confirmation' => $confirmPassword,
            'user_type' => $userType
        ];
        
        $result = $api->register($userData);
        
        if ($result['success']) {
            setFlash('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
            redirect('login.php');
        } else {
            $error = $result['message'] ?? 'Erro ao fazer cadastro.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card card">
        <div class="card-body">
            <div class="auth-logo">
                <h2><i class="fas fa-car text-green"></i> <?= SITE_TITLE ?></h2>
                <p>Crie sua conta e comece a compartilhar caronas</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label for="name" class="form-label">Nome completo</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" 
                           placeholder="(11) 99999-9999" required>
                </div>
                
                <div class="mb-3">
                    <label for="user_type" class="form-label">Tipo de usuário</label>
                    <select class="form-control" id="user_type" name="user_type" required>
                        <option value="">Selecione...</option>
                        <option value="passenger" <?= ($_POST['user_type'] ?? '') === 'passenger' ? 'selected' : '' ?>>
                            Passageiro - Busco caronas
                        </option>
                        <option value="driver" <?= ($_POST['user_type'] ?? '') === 'driver' ? 'selected' : '' ?>>
                            Motorista - Ofereço caronas
                        </option>
                        <option value="both" <?= ($_POST['user_type'] ?? '') === 'both' ? 'selected' : '' ?>>
                            Ambos - Busco e ofereço caronas
                        </option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           minlength="6" required>
                    <div class="form-text">A senha deve ter pelo menos 6 caracteres.</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar senha</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           minlength="6" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>
                    Criar conta
                </button>
                
                <div class="text-center">
                    <p class="mb-0">Já tem uma conta?</p>
                    <a href="login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Fazer login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('phone').addEventListener('input', function(e) {
    let x = e.target.value.replace(/\D/g, '');
    x = x.replace(/(\d{2})(\d)/, '($1) $2');
    x = x.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = x;
});
</script>

<?php require_once 'includes/footer.php'; ?> 