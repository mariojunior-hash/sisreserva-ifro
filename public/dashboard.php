<?php
/**
 * SisReserva IFRO — Dashboard do Usuário (Solicitante)
 * Resumo de reservas, indicadores e atalhos rápidos
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/flash.php';

requireLogin();

$user = currentUser();
$pageTitle = 'Meu Painel';

$counts = [
    'total'     => 0,
    'pendente'  => 0,
    'aprovada'  => 0,
    'recusada'  => 0,
    'cancelada' => 0,
];
$recentReservations = [];

try {
    $pdo = getDBConnection();
    
    // Contadores de reservas do usuário
    $stmt = $pdo->prepare(
        'SELECT status, COUNT(*) as qtd 
         FROM reservations 
         WHERE user_id = :user_id 
         GROUP BY status'
    );
    $stmt->execute([':user_id' => $user['id']]);
    while ($row = $stmt->fetch()) {
        $st = strtolower($row['status']);
        if (isset($counts[$st])) {
            $counts[$st] = (int)$row['qtd'];
        }
        $counts['total'] += (int)$row['qtd'];
    }

    // Últimas 5 reservas do usuário
    $stmt = $pdo->prepare(
        'SELECT r.id, r.start_time, r.end_time, r.purpose, r.status, r.rejection_reason,
                res.name as resource_name, res.category, res.location
         FROM reservations r
         JOIN resources res ON r.resource_id = res.id
         WHERE r.user_id = :user_id
         ORDER BY r.start_time DESC
         LIMIT 5'
    );
    $stmt->execute([':user_id' => $user['id']]);
    $recentReservations = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('[SisReserva Dashboard] Erro: ' . $e->getMessage());
    setFlash('danger', 'Não foi possível carregar os dados do seu painel.');
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Cabeçalho de Boas-vindas -->
<div class="card" style="margin-bottom: 1.5rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="card-title" style="font-size: 1.6rem; margin-bottom: 0.25rem;">
        Olá, <?= e($user['name']) ?>!
      </h1>
      <p style="color: var(--text-muted); font-size: 0.95rem;">
        Matrícula/SIAPE: <strong><?= e($user['matricula']) ?></strong> &bull; Perfil: <span class="badge badge-secondary"><?= e($user['role']) ?></span>
      </p>
    </div>

    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <a href="<?= BASE_URL ?>/reservas/nova.php" class="btn btn-primary">➕ Nova Reserva</a>
      <a href="<?= BASE_URL ?>/reservas/minhas.php" class="btn btn-secondary">📋 Minhas Reservas</a>
      <?php if (isAdmin()): ?>
        <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-warning">⚙️ Painel do Administrador</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Indicadores de Status -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Total de Solicitações</span>
      <span class="stat-value"><?= $counts['total'] ?></span>
    </div>
    <div class="stat-badge" style="background: var(--info-bg); color: var(--info);">📊</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Pendentes</span>
      <span class="stat-value" style="color: var(--warning);"><?= $counts['pendente'] ?></span>
    </div>
    <div class="stat-badge" style="background: var(--warning-bg); color: var(--warning);">⏳</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Aprovadas</span>
      <span class="stat-value" style="color: var(--success);"><?= $counts['aprovada'] ?></span>
    </div>
    <div class="stat-badge" style="background: var(--success-bg); color: var(--success);">✓</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Recusadas</span>
      <span class="stat-value" style="color: var(--danger);"><?= $counts['recusada'] ?></span>
    </div>
    <div class="stat-badge" style="background: var(--danger-bg); color: var(--danger);">✕</div>
  </div>
</div>

<!-- Tabela de Reservas Recentes -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Minhas Reservas Recentes</h2>
    <a href="<?= BASE_URL ?>/reservas/minhas.php" style="font-size: 0.875rem; color: var(--primary); text-decoration: none; font-weight: 600;">
      Ver Histórico Completo &rarr;
    </a>
  </div>

  <?php if (empty($recentReservations)): ?>
    <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
      <p style="font-size: 1.1rem; margin-bottom: 1rem;">Você ainda não possui nenhuma reserva solicitada.</p>
      <a href="<?= BASE_URL ?>/reservas/nova.php" class="btn btn-primary">Fazer Primeira Reserva</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Recurso</th>
            <th>Data e Horário</th>
            <th>Finalidade</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentReservations as $r): ?>
            <?php 
              $isFuture = strtotime($r['start_time']) > time();
              $canCancel = $isFuture && in_array($r['status'], ['PENDENTE', 'APROVADA'], true);
            ?>
            <tr>
              <td>
                <strong><?= e($r['resource_name']) ?></strong>
                <div style="font-size: 0.775rem; color: var(--text-muted);">
                  <?= e($r['category']) ?> &bull; <?= e($r['location']) ?>
                </div>
              </td>
              <td>
                <div>📅 <?= formatDateBr($r['start_time']) ?></div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                  🕒 <?= formatTimeBr($r['start_time']) ?> às <?= formatTimeBr($r['end_time']) ?>
                </div>
              </td>
              <td style="max-width: 250px;">
                <span title="<?= e($r['purpose']) ?>">
                  <?= e(mb_strimwidth($r['purpose'], 0, 50, '...')) ?>
                </span>
                <?php if ($r['status'] === 'RECUSADA' && !empty($r['rejection_reason'])): ?>
                  <div style="font-size: 0.75rem; color: var(--danger); margin-top: 4px;">
                    <strong>Motivo:</strong> <?= e($r['rejection_reason']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= renderReservationBadge($r['status']) ?></td>
              <td>
                <?php if ($canCancel): ?>
                  <form action="<?= BASE_URL ?>/reservas/cancelar.php" method="POST" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Deseja realmente cancelar este agendamento?">
                      Cancelar
                    </button>
                  </form>
                <?php else: ?>
                  <span style="font-size: 0.8rem; color: var(--text-muted);">-</span>
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
