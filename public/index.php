<?php
/**
 * SisReserva IFRO — Página Inicial / Apresentação do Sistema
 * Exibe a situação dos recursos do campus e status da conexão local.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Página Inicial';

// Teste de conexão e busca de recursos cadastrados para consulta pública
$dbStatus = false;
$dbMessage = '';
$resources = [];

try {
    $pdo = getDBConnection();
    $dbStatus = true;
    $dbMessage = 'Conexão com MySQL estabelecida com sucesso via PDO.';
    
    // Consulta recursos para exibição
    $stmt = $pdo->query('SELECT id, name, category, capacity, location, description, status FROM resources ORDER BY category ASC, name ASC');
    $resources = $stmt->fetchAll();
} catch (Exception $e) {
    $dbStatus = false;
    $dbMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="margin-bottom: 2rem;">
  <div style="display: flex; flex-direction: column; gap: 0.75rem;">
    <h1 style="color: var(--primary); font-size: 1.85rem; font-weight: 800;">
      Bem-vindo ao <?= e(APP_NAME) ?>
    </h1>
    <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 800px;">
      Sistema oficial de agendamento e reserva de laboratórios de informática e equipamentos didáticos do IFRO Campus Porto Velho Calama. Evite conflitos de horário e acompanhe suas solicitações em tempo real.
    </p>

    <!-- Indicador de Status da Conexão com o Banco -->
    <div style="margin-top: 0.5rem;">
      <?php if ($dbStatus): ?>
        <span class="badge badge-success" style="padding: 0.4rem 0.8rem; font-size: 0.825rem;">
          ● BANCO DE DADOS CONECTADO (MySQL/MariaDB)
        </span>
      <?php else: ?>
        <span class="badge badge-danger" style="padding: 0.4rem 0.8rem; font-size: 0.825rem;">
          ✕ ERRO DE CONEXÃO COM O BANCO DE DADOS
        </span>
        <p style="color: var(--danger); font-size: 0.85rem; margin-top: 0.5rem;">
          <?= e($dbMessage) ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Ações Rápidas -->
    <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
      <?php if (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-primary">Acessar Meu Painel</a>
        <a href="<?= BASE_URL ?>/reservas/nova.php" class="btn btn-secondary">Solicitar Nova Reserva</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary">Fazer Login</a>
        <a href="<?= BASE_URL ?>/cadastro.php" class="btn btn-secondary">Criar Minha Conta</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Catálogo de Recursos Disponíveis no Campus -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Recursos Institucionais Cadastrados</h2>
    <span class="text-muted" style="font-size: 0.85rem;">Consulta em tempo real</span>
  </div>

  <?php if (empty($resources)): ?>
    <p style="color: var(--text-muted); padding: 1rem 0;">
      Nenhum recurso cadastrado no momento. Execute o script <code>database/seed.sql</code> no seu MySQL.
    </p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Recurso</th>
            <th>Categoria</th>
            <th>Capacidade</th>
            <th>Localização</th>
            <th>Status Operacional</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resources as $res): ?>
            <tr>
              <td>
                <strong><?= e($res['name']) ?></strong>
                <?php if (!empty($res['description'])): ?>
                  <div style="font-size: 0.775rem; color: var(--text-muted); margin-top: 2px;">
                    <?= e($res['description']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= e($res['category']) ?></td>
              <td><?= $res['capacity'] ? e($res['capacity']) . ' pessoas' : '-' ?></td>
              <td><?= e($res['location']) ?></td>
              <td><?= renderResourceBadge($res['status']) ?></td>
              <td>
                <?php if ($res['status'] === 'ATIVO'): ?>
                  <a href="<?= BASE_URL . (isLoggedIn() ? '/reservas/nova.php?recurso_id=' . (int)$res['id'] : '/login.php') ?>" 
                     class="btn btn-sm btn-primary">
                    Reservar
                  </a>
                <?php else: ?>
                  <span style="font-size: 0.8rem; color: var(--text-muted);">Indisponível</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
