<?php
/**
 * SisReserva IFRO — Moderação Geral de Reservas (Admin)
 * Listagem institucional com filtros por status, aprovação e rejeição fundamentada
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireAdmin();

$pageTitle = 'Todas as Reservas (Moderação)';
$filterStatus = strtoupper(trim((string)($_GET['status'] ?? '')));

$reservations = [];
try {
    $pdo = getDBConnection();

    $sql = '
        SELECT r.id, r.start_time, r.end_time, r.purpose, r.status, r.rejection_reason, r.created_at,
               u.name as user_name, u.matricula, u.email,
               res.name as resource_name, res.category, res.location, res.status as resource_status,
               rev.name as reviewer_name
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN resources res ON r.resource_id = res.id
        LEFT JOIN users rev ON r.reviewed_by_id = rev.id
    ';

    if (in_array($filterStatus, ['PENDENTE', 'APROVADA', 'RECUSADA', 'CANCELADA'], true)) {
        $sql .= ' WHERE r.status = :status';
        $sql .= ' ORDER BY r.start_time DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':status' => $filterStatus]);
    } else {
        $sql .= ' ORDER BY r.start_time DESC';
        $stmt = $pdo->query($sql);
    }

    $reservations = $stmt->fetchAll();

} catch (Exception $e) {
    error_log('[SisReserva Reservas Admin] Erro: ' . $e->getMessage());
    setFlash('danger', 'Erro ao carregar a listagem de reservas.');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="card-title">Moderação e Histórico Institucional de Reservas</h1>
      <p style="color: var(--text-muted); font-size: 0.875rem;">
        Avalie solicitações pendentes, acompanhe aprovações e audite motivos de recusa.
      </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
      <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-secondary">&larr; Painel</a>
    </div>
  </div>

  <!-- Barra de Filtros -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
    <a href="<?= BASE_URL ?>/admin/reservas.php" class="btn btn-sm <?= empty($filterStatus) ? 'btn-primary' : 'btn-secondary' ?>">
      Todas
    </a>
    <a href="<?= BASE_URL ?>/admin/reservas.php?status=PENDENTE" class="btn btn-sm <?= $filterStatus === 'PENDENTE' ? 'btn-primary' : 'btn-secondary' ?>">
      Pendentes
    </a>
    <a href="<?= BASE_URL ?>/admin/reservas.php?status=APROVADA" class="btn btn-sm <?= $filterStatus === 'APROVADA' ? 'btn-primary' : 'btn-secondary' ?>">
      Aprovadas
    </a>
    <a href="<?= BASE_URL ?>/admin/reservas.php?status=RECUSADA" class="btn btn-sm <?= $filterStatus === 'RECUSADA' ? 'btn-primary' : 'btn-secondary' ?>">
      Recusadas
    </a>
    <a href="<?= BASE_URL ?>/admin/reservas.php?status=CANCELADA" class="btn btn-sm <?= $filterStatus === 'CANCELADA' ? 'btn-primary' : 'btn-secondary' ?>">
      Canceladas
    </a>
  </div>

  <?php if (empty($reservations)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
      <p style="font-size: 1.1rem;">Nenhuma reserva encontrada para o filtro selecionado.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Protocolo</th>
            <th>Solicitante</th>
            <th>Recurso</th>
            <th>Data e Horário</th>
            <th>Finalidade</th>
            <th>Status</th>
            <th>Ações da Coordenação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservations as $r): ?>
            <?php 
              $isFuture = strtotime($r['start_time']) > time();
              $isPending = ($r['status'] === 'PENDENTE');
            ?>
            <tr>
              <td>#<?= (int)$r['id'] ?></td>
              <td>
                <strong><?= e($r['user_name']) ?></strong>
                <div style="font-size: 0.775rem; color: var(--text-muted);">
                  Matrícula: <?= e($r['matricula']) ?>
                </div>
              </td>
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
                <div><?= e($r['purpose']) ?></div>
                <?php if ($r['status'] === 'RECUSADA' && !empty($r['rejection_reason'])): ?>
                  <div style="margin-top: 0.35rem; padding: 0.35rem 0.5rem; background: var(--danger-bg); border-left: 3px solid var(--danger); font-size: 0.75rem; color: var(--danger); border-radius: 4px;">
                    <strong>Motivo da Recusa:</strong> <?= e($r['rejection_reason']) ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($r['reviewer_name'])): ?>
                  <div style="font-size: 0.725rem; color: var(--text-muted); margin-top: 2px;">
                    Avaliador: <?= e($r['reviewer_name']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= renderReservationBadge($r['status']) ?></td>
              <td style="white-space: nowrap;">
                <?php if ($isPending): ?>
                  <!-- Ação: Aprovar -->
                  <form action="<?= BASE_URL ?>/admin/aprovar.php" method="POST" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary" data-confirm="Confirmar a aprovação desta reserva?">
                      ✓ Aprovar
                    </button>
                  </form>

                  <!-- Ação: Rejeitar com Modal -->
                  <button type="button" class="btn btn-sm btn-danger" 
                          onclick="document.getElementById('modal-rej-<?= (int)$r['id'] ?>').style.display='block';">
                    ✕ Rejeitar
                  </button>

                  <!-- Modal de Rejeição -->
                  <div id="modal-rej-<?= (int)$r['id'] ?>" 
                       style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; padding: 2rem;">
                    <div style="background: #fff; max-width: 500px; margin: 3rem auto; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-lg);">
                      <h3 style="margin-bottom: 0.75rem; font-size: 1.2rem; color: var(--danger);">Rejeitar Reserva #<?= (int)$r['id'] ?></h3>
                      <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
                        O motivo da rejeição deve possuir pelo menos 10 caracteres (Seção 13).
                      </p>

                      <form action="<?= BASE_URL ?>/admin/rejeitar.php" method="POST" class="form-rejeitar-reserva">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                        <div class="form-group">
                          <label class="form-label">Motivo da Rejeição *</label>
                          <textarea name="rejection_reason" class="form-control" rows="3" 
                                    placeholder="Ex: Espaço físico já comprometido com atividade acadêmica previamente agendada." required minlength="10"></textarea>
                          <div class="form-hint">Mínimo de 10 caracteres explicativos.</div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                          <button type="button" class="btn btn-secondary btn-sm" 
                                  onclick="document.getElementById('modal-rej-<?= (int)$r['id'] ?>').style.display='none';">
                            Cancelar
                          </button>
                          <button type="submit" class="btn btn-danger btn-sm">
                            Confirmar Rejeição
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>

                <?php elseif ($r['status'] === 'APROVADA' && $isFuture): ?>
                  <!-- Cancelamento administrativo por motivo de força maior -->
                  <form action="<?= BASE_URL ?>/reservas/cancelar.php" method="POST" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="redirect_admin" value="1">
                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Deseja cancelar esta reserva previamente aprovada?">
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
