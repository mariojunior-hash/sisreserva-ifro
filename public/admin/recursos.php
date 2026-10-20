<?php
/**
 * SisReserva IFRO — Gestão de Recursos Didáticos e Laboratórios
 * Listagem, filtragem e ações administrativas de edição e exclusão
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireAdmin();

$pageTitle = 'Gerenciamento de Recursos';

$resources = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT id, name, category, capacity, location, description, status, created_at FROM resources ORDER BY category ASC, name ASC');
    $resources = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('[SisReserva Recursos Admin] Erro: ' . $e->getMessage());
    setFlash('danger', 'Erro ao carregar o catálogo de recursos.');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="card-title">Catálogo de Recursos do Campus</h1>
      <p style="color: var(--text-muted); font-size: 0.875rem;">
        Cadastre, altere status e gerencie os laboratórios e equipamentos disponíveis para reserva.
      </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
      <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-secondary">&larr; Painel</a>
      <a href="<?= BASE_URL ?>/admin/recurso_novo.php" class="btn btn-primary">➕ Novo Recurso</a>
    </div>
  </div>

  <?php if (empty($resources)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
      <p style="font-size: 1.1rem; margin-bottom: 1rem;">Nenhum recurso cadastrado no sistema.</p>
      <a href="<?= BASE_URL ?>/admin/recurso_novo.php" class="btn btn-primary">Cadastrar Primeiro Recurso</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome do Recurso</th>
            <th>Categoria</th>
            <th>Capacidade</th>
            <th>Localização</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resources as $res): ?>
            <tr>
              <td>#<?= (int)$res['id'] ?></td>
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
              <td style="white-space: nowrap;">
                <a href="<?= BASE_URL ?>/admin/recurso_editar.php?id=<?= (int)$res['id'] ?>" class="btn btn-sm btn-secondary">
                  ✏️ Editar
                </a>

                <form action="<?= BASE_URL ?>/admin/recurso_excluir.php" method="POST" style="display: inline;">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= (int)$res['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" data-confirm="Tem certeza de que deseja excluir este recurso? Todas as reservas associadas também serão removidas.">
                    🗑️ Excluir
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
