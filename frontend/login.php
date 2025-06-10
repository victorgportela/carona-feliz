<?php
require_once 'includes/config.php';
require_once 'includes/api_client.php';
$title = 'Login - ' . SITE_TITLE;

$api = new ApiClient();

// Redirecionar se já estiver logado
if ($api->isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (!isValidEmail($email)) {
        $error = 'Por favor, insira um email válido.';
    } else {
        $result = $api->login($email, $password);
        
        if ($result['success']) {
            setFlash('success', 'Login realizado com sucesso!');
            redirect('dashboard.php');
        } else {
            $error = $result['message'] ?? 'Erro ao fazer login.';
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
                <p>Conectando pessoas através de caronas</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Entrar
                </button>
                
                <div class="text-center">
                    <p class="mb-0">Ainda não tem uma conta?</p>
                    <a href="register.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i>
                        Criar conta
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 