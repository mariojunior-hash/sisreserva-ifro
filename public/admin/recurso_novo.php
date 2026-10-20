<?php
/**
 * SisReserva IFRO — Cadastro de Novo Recurso (Admin)
 * Formulário para inclusão de novos laboratórios ou equipamentos didáticos
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireAdmin();

$pageTitle = 'Cadastrar Novo Recurso';

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 650px; margin: 1.5rem auto;">
  <div class="card">
    <div class="card-header">
      <h1 class="card-title">Cadastrar Novo Recurso</h1>
      <a href="<?= BASE_URL ?>/admin/recursos.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">&larr; Voltar à Lista</a>
    </div>

    <form action="<?= BASE_URL ?>/admin/recurso_salvar.php" method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="0">

      <div class="form-group">
        <label for="name" class="form-label">Nome do Recurso *</label>
        <input type="text" id="name" name="name" class="form-control" 
               placeholder="Ex: Labin 5, Projetor BenQ 02, Kit Robótica VEX" required autofocus>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="category" class="form-label">Categoria *</label>
          <select id="category" name="category" class="form-control" required>
            <option value="Laboratório">Laboratório</option>
            <option value="Equipamento">Equipamento</option>
            <option value="Sala Especial">Sala Especial</option>
          </select>
        </div>

        <div class="form-group">
          <label for="capacity" class="form-label">Capacidade Máxima (Pessoas)</label>
          <input type="number" id="capacity" name="capacity" class="form-control" 
               min="1" placeholder="Ex: 35 (deixe vazio se equipamento)">
          <div class="form-hint">Obrigatório apenas para salas e laboratórios.</div>
        </div>
      </div>

      <div class="form-group">
        <label for="location" class="form-label">Localização Física no Campus *</label>
        <input type="text" id="location" name="location" class="form-control" 
               placeholder="Ex: Bloco A - Sala 105 ou COGETI" required>
      </div>

      <div class="form-group">
        <label for="status" class="form-label">Status Operacional *</label>
        <select id="status" name="status" class="form-control" required>
          <option value="ATIVO" selected>ATIVO (Disponível para reservas)</option>
          <option value="MANUTENCAO">MANUTENCAO (Bloqueado para manutenções técnicas)</option>
          <option value="INATIVO">INATIVO (Indisponível temporariamente)</option>
        </select>
      </div>

      <div class="form-group">
        <label for="description" class="form-label">Descrição Técnica e Observações</label>
        <textarea id="description" name="description" class="form-control" rows="3" 
                  placeholder="Ex: 30 Computadores Core i5, 16GB RAM, projetor teto, ar-condicionado duplo."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
        <a href="<?= BASE_URL ?>/admin/recursos.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Recurso</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
