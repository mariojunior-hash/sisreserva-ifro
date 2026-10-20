<?php
/**
 * SisReserva IFRO — Cadastro de Novos Usuários (Solicitantes)
 * Validações no servidor, prevenção de duplicidade e hashing seguro de senhas
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/flash.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$pageTitle = 'Criar Nova Conta';

$name = '';
$email = '';
$matricula = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('danger', 'Token de segurança inválido. Tente enviar o formulário novamente.');
        header('Location: ' . BASE_URL . '/cadastro.php');
        exit;
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $email = normalizeEmail((string)($_POST['email'] ?? ''));
    $matricula = strtoupper(trim((string)($_POST['matricula'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $password_confirm = (string)($_POST['password_confirm'] ?? '');

    // Validações no Servidor
    if (empty($name) || empty($email) || empty($matricula) || empty($password) || empty($password_confirm)) {
        setFlash('danger', 'Todos os campos são obrigatórios.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', 'O formato do e-mail institucional informado é inválido.');
    } elseif (strlen($password) < 6) {
        setFlash('danger', 'A senha deve conter no mínimo 6 caracteres.');
    } elseif ($password !== $password_confirm) {
        setFlash('danger', 'A confirmação de senha não confere com a senha digitada.');
    } else {
        try {
            $pdo = getDBConnection();

            // 1. Verifica duplicidade de e-mail (Cenário 1)
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                setFlash('danger', 'Este e-mail já está cadastrado no sistema. Tente fazer login ou use outro.');
            } else {
                // 2. Verifica duplicidade de matrícula
                $stmt = $pdo->prepare('SELECT id FROM users WHERE matricula = :matricula LIMIT 1');
                $stmt->execute([':matricula' => $matricula]);
                if ($stmt->fetch()) {
                    setFlash('danger', 'Esta matrícula já está cadastrada no sistema.');
                } else {
                    // 3. Cadastra o novo usuário com hash seguro
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare(
                        'INSERT INTO users (name, email, matricula, password_hash, role) 
                         VALUES (:name, :email, :matricula, :hash, "SOLICITANTE")'
                    );
                    $stmt->execute([
                        ':name'      => $name,
                        ':email'     => $email,
                        ':matricula' => $matricula,
                        ':hash'      => $hash,
                    ]);

                    setFlash('success', 'Cadastro realizado com sucesso! Utilize seu e-mail e senha para entrar.');
                    header('Location: ' . BASE_URL . '/login.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log('[SisReserva Cadastro] Erro: ' . $e->getMessage());
            setFlash('danger', 'Ocorreu um erro ao realizar o cadastro. Tente novamente mais tarde.');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 520px; margin: 2rem auto;">
  <div class="card">
    <div class="card-header" style="text-align: center; justify-content: center;">
      <h1 class="card-title" style="font-size: 1.5rem;">Cadastro de Solicitante</h1>
    </div>

    <form action="<?= BASE_URL ?>/cadastro.php" method="POST">
      <?= csrfField() ?>

      <div class="form-group">
        <label for="name" class="form-label">Nome Completo</label>
        <input type="text" id="name" name="name" class="form-control" 
               placeholder="Ex: Carlos Alberto da Silva" value="<?= e($name) ?>" required autofocus>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">E-mail Institucional</label>
        <input type="email" id="email" name="email" class="form-control" 
               placeholder="nome.sobrenome@ifro.edu.br" value="<?= e($email) ?>" required>
        <div class="form-hint">Utilize seu e-mail institucional do IFRO.</div>
      </div>

      <div class="form-group">
        <label for="matricula" class="form-label">Matrícula Discente ou SIAPE</label>
        <input type="text" id="matricula" name="matricula" class="form-control" 
               placeholder="Ex: 2026110001 ou SIAPE" value="<?= e($matricula) ?>" required>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Senha de Acesso</label>
        <input type="password" id="password" name="password" class="form-control" 
               placeholder="Mínimo de 6 caracteres" required>
      </div>

      <div class="form-group">
        <label for="password_confirm" class="form-label">Confirmação de Senha</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control" 
               placeholder="Repita a senha digitada" required>
      </div>

      <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="width: 100%;">
          Cadastrar Minha Conta
        </button>
      </div>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid var(--border); padding-top: 1rem; font-size: 0.9rem;">
      <p style="color: var(--text-muted);">
        Já possui conta cadastrada? 
        <a href="<?= BASE_URL ?>/login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">
          Fazer Login
        </a>
      </p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
