<?php
/**
 * SisReserva IFRO — Formulário de Solicitação de Nova Reserva
 * Agendamento de laboratórios ou equipamentos com regras institucionais
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireLogin();

$pageTitle = 'Solicitar Nova Reserva';
$selectedResourceId = (int)($_GET['recurso_id'] ?? 0);

$resources = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT id, name, category, capacity, location, status FROM resources ORDER BY category ASC, name ASC');
    $resources = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('[SisReserva Nova Reserva] Erro ao carregar recursos: ' . $e->getMessage());
    setFlash('danger', 'Não foi possível carregar a lista de recursos.');
}

// Data mínima: hoje; Data máxima: hoje + 30 dias
$minDate = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+30 days'));

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 680px; margin: 1.5rem auto;">
  <div class="card">
    <div class="card-header">
      <h1 class="card-title">Nova Solicitação de Reserva</h1>
      <a href="<?= BASE_URL ?>/dashboard.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">&larr; Voltar ao Painel</a>
    </div>

    <!-- Regras Institucionais resumidas -->
    <div style="background-color: var(--primary-light); padding: 0.875rem 1.25rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.85rem; color: var(--primary-dark);">
      <strong>📋 Regras de Agendamento (IFRO Calama):</strong>
      <ul style="margin-top: 0.35rem; margin-left: 1.25rem; line-height: 1.4;">
        <li>Antecedência mínima de <strong>2 horas</strong> e máxima de <strong>30 dias</strong>.</li>
        <li>Duração mínima de <strong>45 minutos</strong> e máxima de <strong>4 horas contínuas</strong>.</li>
        <li>Recursos em manutenção ou inativos não podem ser reservados.</li>
      </ul>
    </div>

    <form action="<?= BASE_URL ?>/reservas/criar.php" method="POST">
      <?= csrfField() ?>

      <div class="form-group">
        <label for="resource_id" class="form-label">Recurso Desejado (Laboratório / Equipamento) *</label>
        <select id="resource_id" name="resource_id" class="form-control" required>
          <option value="">-- Selecione um Recurso Disponível --</option>
          <?php foreach ($resources as $res): ?>
            <?php 
              $isAvailable = ($res['status'] === 'ATIVO');
              $isSelected = ($selectedResourceId === (int)$res['id']);
            ?>
            <option value="<?= (int)$res['id'] ?>" 
                    <?= $isSelected ? 'selected' : '' ?> 
                    <?= !$isAvailable ? 'disabled' : '' ?>>
              <?= e($res['name']) ?> (<?= e($res['category']) ?>) 
              <?= !$isAvailable ? '— [' . e($res['status']) . ' - INDISPONÍVEL]' : '— ' . e($res['location']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="date" class="form-label">Data da Reserva *</label>
        <input type="date" id="date" name="date" class="form-control" 
               min="<?= $minDate ?>" max="<?= $maxDate ?>" value="<?= $minDate ?>" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="start_time" class="form-label">Horário Inicial *</label>
          <input type="time" id="start_time" name="start_time" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="end_time" class="form-label">Horário Final *</label>
          <input type="time" id="end_time" name="end_time" class="form-control" required>
        </div>
      </div>

      <div class="form-group">
        <label for="purpose" class="form-label">Finalidade / Justificativa da Atividade *</label>
        <textarea id="purpose" name="purpose" class="form-control" rows="4" 
                  placeholder="Ex: Aula prática da disciplina de Programação Web II com a turma 3B do Curso Técnico Integrado em Informática." required></textarea>
        <div class="form-hint">Descreva a atividade pedagógica ou evento institucional que justificará a reserva.</div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Submeter Solicitação</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
