<?php
/**
 * SisReserva IFRO — Edição de Recurso (Admin)
 * Formulário para atualização de dados e alteração de status operacional
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Recurso não especificado para edição.');
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;
}

$resource = null;
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id, name, category, capacity, location, description, status FROM resources WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $resource = $stmt->fetch();
} catch (Exception $e) {
    error_log('[SisReserva Editar Recurso] Erro: ' . $e->getMessage());
}

if (!$resource) {
    setFlash('danger', 'Recurso não encontrado.');
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;
}

$pageTitle = 'Editar Recurso #' . $id;

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 650px; margin: 1.5rem auto;">
  <div class="card">
    <div class="card-header">
      <h1 class="card-title">Editar Recurso #<?= (int)$resource['id'] ?></h1>
      <a href="<?= BASE_URL ?>/admin/recursos.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">&larr; Voltar à Lista</a>
    </div>

    <form action="<?= BASE_URL ?>/admin/recurso_salvar.php" method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= (int)$resource['id'] ?>">

      <div class="form-group">
        <label for="name" class="form-label">Nome do Recurso *</label>
        <input type="text" id="name" name="name" class="form-control" 
               value="<?= e($resource['name']) ?>" required autofocus>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="category" class="form-label">Categoria *</label>
          <select id="category" name="category" class="form-control" required>
            <option value="Laboratório" <?= $resource['category'] === 'Laboratório' ? 'selected' : '' ?>>Laboratório</option>
            <option value="Equipamento" <?= $resource['category'] === 'Equipamento' ? 'selected' : '' ?>>Equipamento</option>
            <option value="Sala Especial" <?= $resource['category'] === 'Sala Especial' ? 'selected' : '' ?>>Sala Especial</option>
          </select>
        </div>

        <div class="form-group">
          <label for="capacity" class="form-label">Capacidade Máxima (Pessoas)</label>
          <input type="number" id="capacity" name="capacity" class="form-control" 
               min="1" value="<?= $resource['capacity'] ? (int)$resource['capacity'] : '' ?>" placeholder="Ex: 35">
        </div>
      </div>

      <div class="form-group">
        <label for="location" class="form-label">Localização Física no Campus *</label>
        <input type="text" id="location" name="location" class="form-control" 
               value="<?= e($resource['location']) ?>" required>
      </div>

      <div class="form-group">
        <label for="status" class="form-label">Status Operacional *</label>
        <select id="status" name="status" class="form-control" required>
          <option value="ATIVO" <?= $resource['status'] === 'ATIVO' ? 'selected' : '' ?>>ATIVO (Disponível)</option>
          <option value="MANUTENCAO" <?= $resource['status'] === 'MANUTENCAO' ? 'selected' : '' ?>>MANUTENCAO (Bloqueado)</option>
          <option value="INATIVO" <?= $resource['status'] === 'INATIVO' ? 'selected' : '' ?>>INATIVO (Indisponível)</option>
        </select>
      </div>

      <div class="form-group">
        <label for="description" class="form-label">Descrição Técnica e Observações</label>
        <textarea id="description" name="description" class="form-control" rows="3"><?= e($resource['description']) ?></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
        <a href="<?= BASE_URL ?>/admin/recursos.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Atualizar Recurso</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
