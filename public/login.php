<?php
/**
 * SisReserva IFRO — Autenticação de Usuários (Login)
 * Validação segura de credenciais com password_verify() e proteção CSRF
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/flash.php';

// Se já estiver autenticado, redireciona diretamente ao dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$pageTitle = 'Entrar no Sistema';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('danger', 'Sessão expirada ou token de segurança inválido. Tente novamente.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    $email = normalizeEmail((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        setFlash('danger', 'Por favor, preencha todos os campos obrigatórios.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare('SELECT id, name, email, matricula, password_hash, role FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                loginUser($user);
                setFlash('success', 'Bem-vindo de volta, ' . e($user['name']) . '!');
                
                if ($user['role'] === 'ADMIN') {
                    header('Location: ' . BASE_URL . '/admin/index.php');
                } else {
                    header('Location: ' . BASE_URL . '/dashboard.php');
                }
                exit;
            } else {
                setFlash('danger', 'E-mail ou senha incorretos. Verifique suas credenciais.');
            }
        } catch (Exception $e) {
            error_log('[SisReserva Login] Erro: ' . $e->getMessage());
            setFlash('danger', 'Ocorreu um erro ao processar a autenticação. Tente novamente mais tarde.');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 480px; margin: 2rem auto;">
  <div class="card">
    <div class="card-header" style="text-align: center; justify-content: center;">
      <h1 class="card-title" style="font-size: 1.5rem;">Acesse sua Conta</h1>
    </div>

    <form action="<?= BASE_URL ?>/login.php" method="POST">
      <?= csrfField() ?>

      <div class="form-group">
        <label for="email" class="form-label">E-mail Institucional</label>
        <input type="email" id="email" name="email" class="form-control" 
               placeholder="exemplo@ifro.edu.br" value="<?= e($email) ?>" required autofocus>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Senha de Acesso</label>
        <input type="password" id="password" name="password" class="form-control" 
               placeholder="Digite sua senha" required>
      </div>

      <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="width: 100%;">
          Entrar no Sistema
        </button>
      </div>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid var(--border); padding-top: 1rem; font-size: 0.9rem;">
      <p style="color: var(--text-muted);">
        Ainda não possui cadastro institucional? 
        <a href="<?= BASE_URL ?>/cadastro.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">
          Criar Conta
        </a>
      </p>
    </div>
  </div>

  <!-- Dica de demonstração para testes rápidos -->
  <div class="card" style="background-color: var(--primary-light); border-color: #a7f3d0; font-size: 0.85rem;">
    <strong>ℹ️ Credenciais para Demonstração:</strong>
    <ul style="margin-top: 0.5rem; margin-left: 1.25rem;">
      <li><strong>Admin:</strong> admin@ifro.edu.br | Senha: <code>Admin@123</code></li>
      <li><strong>Professor:</strong> professor@ifro.edu.br | Senha: <code>Professor@123</code></li>
      <li><strong>Aluna:</strong> maria.aluna@ifro.edu.br | Senha: <code>Aluno@123</code></li>
    </ul>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
