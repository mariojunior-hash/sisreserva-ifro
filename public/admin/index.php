<?php
/**
 * SisReserva IFRO — Painel Administrativo Geral (Admin Index)
 * Visão consolidada, métricas operacionais e fila de moderação de reservas
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireAdmin();

$pageTitle = 'Painel Administrativo';

$metrics = [
    'recursos_total'      => 0,
    'recursos_ativos'     => 0,
    'recursos_manutencao' => 0,
    'reservas_total'      => 0,
    'reservas_pendentes'  => 0,
    'reservas_aprovadas'  => 0,
    'reservas_recusadas'  => 0,
];
$pendingReservations = [];

try {
    $pdo = getDBConnection();

    // Métricas de Recursos
    $stmt = $pdo->query('SELECT status, COUNT(*) as qtd FROM resources GROUP BY status');
    while ($row = $stmt->fetch()) {
        $st = strtoupper($row['status']);
        $qtd = (int)$row['qtd'];
        $metrics['recursos_total'] += $qtd;
        if ($st === 'ATIVO') $metrics['recursos_ativos'] = $qtd;
        if ($st === 'MANUTENCAO') $metrics['recursos_manutencao'] = $qtd;
    }

    // Métricas de Reservas
    $stmt = $pdo->query('SELECT status, COUNT(*) as qtd FROM reservations GROUP BY status');
    while ($row = $stmt->fetch()) {
        $st = strtoupper($row['status']);
        $qtd = (int)$row['qtd'];
        $metrics['reservas_total'] += $qtd;
        if ($st === 'PENDENTE') $metrics['reservas_pendentes'] = $qtd;
        if ($st === 'APROVADA') $metrics['reservas_aprovadas'] = $qtd;
        if ($st === 'RECUSADA') $metrics['reservas_recusadas'] = $qtd;
    }

    // Solicitações Pendentes Recentes
    $stmt = $pdo->query(
        'SELECT r.id, r.start_time, r.end_time, r.purpose, r.created_at,
                u.name as user_name, u.matricula, u.email,
                res.name as resource_name, res.category, res.status as resource_status
         FROM reservations r
         JOIN users u ON r.user_id = u.id
         JOIN resources res ON r.resource_id = res.id
         WHERE r.status = "PENDENTE"
         ORDER BY r.start_time ASC
         LIMIT 10'
    );
    $pendingReservations = $stmt->fetchAll();

} catch (Exception $e) {
    error_log('[SisReserva Admin Index] Erro: ' . $e->getMessage());
    setFlash('danger', 'Erro ao carregar os dados administrativos.');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Cabeçalho do Admin -->
<div class="card" style="margin-bottom: 1.5rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="card-title" style="font-size: 1.6rem; color: var(--primary);">
        Painel de Gestão e Moderação
      </h1>
      <p style="color: var(--text-muted); font-size: 0.95rem;">
        Coordenação de Laboratórios e Equipamentos Didáticos &bull; IFRO Calama
      </p>
    </div>

    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <a href="<?= BASE_URL ?>/admin/recursos.php" class="btn btn-secondary">📦 Gerenciar Recursos</a>
      <a href="<?= BASE_URL ?>/admin/recurso_novo.php" class="btn btn-primary">➕ Novo Recurso</a>
      <a href="<?= BASE_URL ?>/admin/reservas.php" class="btn btn-warning">📝 Todas as Reservas</a>
    </div>
  </div>
</div>

<!-- Indicadores Estatísticos Consolidados (Seção 22) -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Total de Recursos</span>
      <span class="stat-value"><?= $metrics['recursos_total'] ?></span>
      <span style="font-size: 0.75rem; color: var(--text-muted);">
        <?= $metrics['recursos_ativos'] ?> ativos | <?= $metrics['recursos_manutencao'] ?> manutenção
      </span>
    </div>
    <div class="stat-badge" style="background: var(--info-bg); color: var(--info);">🏢</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Reservas Pendentes</span>
      <span class="stat-value" style="color: var(--warning);"><?= $metrics['reservas_pendentes'] ?></span>
      <span style="font-size: 0.75rem; color: var(--warning);">Aguardando decisão</span>
    </div>
    <div class="stat-badge" style="background: var(--warning-bg); color: var(--warning);">⏳</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Reservas Aprovadas</span>
      <span class="stat-value" style="color: var(--success);"><?= $metrics['reservas_aprovadas'] ?></span>
      <span style="font-size: 0.75rem; color: var(--success);">Agendamentos ativos</span>
    </div>
    <div class="stat-badge" style="background: var(--success-bg); color: var(--success);">✓</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Reservas Recusadas</span>
      <span class="stat-value" style="color: var(--danger);"><?= $metrics['reservas_recusadas'] ?></span>
      <span style="font-size: 0.75rem; color: var(--danger);">Com justificativa</span>
    </div>
    <div class="stat-badge" style="background: var(--danger-bg); color: var(--danger);">✕</div>
  </div>
</div>

<!-- Fila de Moderação Imediata -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title">Fila de Solicitações Pendentes</h2>
    <span class="badge badge-warning"><?= count($pendingReservations) ?> na fila</span>
  </div>

  <?php if (empty($pendingReservations)): ?>
    <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
      <p style="font-size: 1.1rem;">🎉 Não há nenhuma solicitação pendente de moderação no momento!</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Protocolo / Solicitante</th>
            <th>Recurso</th>
            <th>Data e Horário</th>
            <th>Finalidade</th>
            <th>Decisão Rápida</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingReservations as $p): ?>
            <tr>
              <td>
                <div style="font-size: 0.75rem; color: var(--text-muted);">#<?= (int)$p['id'] ?></div>
                <strong><?= e($p['user_name']) ?></strong>
                <div style="font-size: 0.775rem; color: var(--text-muted);">
                  Matrícula: <?= e($p['matricula']) ?>
                </div>
              </td>
              <td>
                <strong><?= e($p['resource_name']) ?></strong>
                <div style="font-size: 0.775rem;">
                  <?= renderResourceBadge($p['resource_status']) ?>
                </div>
              </td>
              <td>
                <div>📅 <?= formatDateBr($p['start_time']) ?></div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                  🕒 <?= formatTimeBr($p['start_time']) ?> às <?= formatTimeBr($p['end_time']) ?>
                </div>
              </td>
              <td style="max-width: 250px;">
                <div title="<?= e($p['purpose']) ?>"><?= e(mb_strimwidth($p['purpose'], 0, 60, '...')) ?></div>
              </td>
              <td style="white-space: nowrap;">
                <!-- Formulário de Aprovação -->
                <form action="<?= BASE_URL ?>/admin/aprovar.php" method="POST" style="display: inline;">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-primary" data-confirm="Confirmar aprovação deste agendamento?">
                    ✓ Aprovar
                  </button>
                </form>

                <!-- Botão que abre modal ou formulário de Rejeição -->
                <button type="button" class="btn btn-sm btn-danger" 
                        onclick="document.getElementById('modal-rejeitar-<?= (int)$p['id'] ?>').style.display='block';">
                  ✕ Rejeitar
                </button>

                <!-- Modal de Justificativa de Rejeição (mínimo 10 caracteres) -->
                <div id="modal-rejeitar-<?= (int)$p['id'] ?>" 
                     style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; padding: 2rem;">
                  <div style="background: #fff; max-width: 500px; margin: 3rem auto; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-lg);">
                    <h3 style="margin-bottom: 0.75rem; font-size: 1.2rem; color: var(--danger);">Rejeitar Reserva #<?= (int)$p['id'] ?></h3>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
                      Para rejeitar, é obrigatório informar o motivo ao solicitante (mínimo 10 caracteres).
                    </p>

                    <form action="<?= BASE_URL ?>/admin/rejeitar.php" method="POST" class="form-rejeitar-reserva">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

                      <div class="form-group">
                        <label class="form-label">Motivo da Rejeição *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" 
                                  placeholder="Ex: Laboratório indisponível devido a manutenção preventiva emergencial." required minlength="10"></textarea>
                        <div class="form-hint">Mínimo de 10 caracteres explicativos.</div>
                      </div>

                      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary btn-sm" 
                                onclick="document.getElementById('modal-rejeitar-<?= (int)$p['id'] ?>').style.display='none';">
                          Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger btn-sm">
                          Confirmar Rejeição
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
