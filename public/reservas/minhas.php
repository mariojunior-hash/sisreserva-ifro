<?php
/**
 * SisReserva IFRO — Listagem de Minhas Reservas (Solicitante)
 * Histórico completo com status, justificativas de recusa e cancelamento de futuras
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireLogin();

$user = currentUser();
$pageTitle = 'Minhas Reservas';

$reservations = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT r.id, r.start_time, r.end_time, r.purpose, r.status, r.rejection_reason, r.created_at,
                res.name as resource_name, res.category, res.location
         FROM reservations r
         JOIN resources res ON r.resource_id = res.id
         WHERE r.user_id = :user_id
         ORDER BY r.start_time DESC'
    );
    $stmt->execute([':user_id' => $user['id']]);
    $reservations = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('[SisReserva Minhas Reservas] Erro: ' . $e->getMessage());
    setFlash('danger', 'Não foi possível carregar suas reservas.');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="card-title">Histórico de Minhas Reservas</h1>
      <p style="color: var(--text-muted); font-size: 0.875rem;">
        Acompanhe a moderação e situação das suas solicitações no IFRO Calama.
      </p>
    </div>
    <a href="<?= BASE_URL ?>/reservas/nova.php" class="btn btn-primary">➕ Nova Reserva</a>
  </div>

  <?php if (empty($reservations)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
      <p style="font-size: 1.1rem; margin-bottom: 1rem;">Nenhuma solicitação de reserva encontrada no seu histórico.</p>
      <a href="<?= BASE_URL ?>/reservas/nova.php" class="btn btn-primary">Solicitar Agendamento Agora</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Protocolo / Recurso</th>
            <th>Data e Horário</th>
            <th>Finalidade</th>
            <th>Status</th>
            <th>Solicitado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservations as $r): ?>
            <?php 
              $isFuture = strtotime($r['start_time']) > time();
              $canCancel = $isFuture && in_array($r['status'], ['PENDENTE', 'APROVADA'], true);
            ?>
            <tr>
              <td>
                <div style="font-size: 0.75rem; color: var(--text-muted);">#<?= (int)$r['id'] ?></div>
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
              <td style="max-width: 280px;">
                <div><?= e($r['purpose']) ?></div>
                <?php if ($r['status'] === 'RECUSADA' && !empty($r['rejection_reason'])): ?>
                  <div style="margin-top: 0.35rem; padding: 0.4rem 0.6rem; background: var(--danger-bg); border-left: 3px solid var(--danger); border-radius: 4px; font-size: 0.775rem; color: var(--danger);">
                    <strong>Justificativa da Administração:</strong><br>
                    <?= e($r['rejection_reason']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= renderReservationBadge($r['status']) ?></td>
              <td style="font-size: 0.8rem; color: var(--text-muted);">
                <?= formatDateTimeBr($r['created_at']) ?>
              </td>
              <td>
                <?php if ($canCancel): ?>
                  <form action="<?= BASE_URL ?>/reservas/cancelar.php" method="POST" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Tem certeza de que deseja cancelar esta reserva?">
                      Cancelar
                    </button>
                  </form>
                <?php elseif (!$isFuture): ?>
                  <span style="font-size: 0.75rem; color: var(--text-muted);">Encerrada</span>
                <?php else: ?>
                  <span style="font-size: 0.75rem; color: var(--text-muted);">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
