<?php
/**
 * SisReserva IFRO — Cabeçalho Padrão (Header)
 * Inicia a estrutura HTML5, Navbar Responsiva e Renderização de Mensagens Flash
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash.php';

$pageTitle = $pageTitle ?? APP_NAME;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?= e($pageTitle) ?> — <?= e(APP_INSTITUTION) ?></title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body>

<header class="navbar">
  <div class="navbar-container">
    <a href="<?= BASE_URL . (isLoggedIn() ? '/dashboard.php' : '/index.php') ?>" class="navbar-brand">
      <span>🏛️ <?= e(APP_NAME) ?></span>
      <span class="navbar-brand-badge">IFRO Calama</span>
    </a>

    <button type="button" class="menu-toggle" aria-label="Abrir menu de navegação">☰</button>

    <nav>
      <ul class="navbar-menu">
        <?php if (isLoggedIn()): ?>
          <li><a href="<?= BASE_URL ?>/dashboard.php" class="nav-link">Dashboard</a></li>
          <li><a href="<?= BASE_URL ?>/reservas/nova.php" class="nav-link">Nova Reserva</a></li>
          <li><a href="<?= BASE_URL ?>/reservas/minhas.php" class="nav-link">Minhas Reservas</a></li>

          <?php if (isAdmin()): ?>
            <li><a href="<?= BASE_URL ?>/admin/index.php" class="nav-link">Painel Admin</a></li>
            <li><a href="<?= BASE_URL ?>/admin/recursos.php" class="nav-link">Recursos</a></li>
            <li><a href="<?= BASE_URL ?>/admin/reservas.php" class="nav-link">Moderação</a></li>
          <?php endif; ?>

          <li class="nav-user">
            <div class="user-badge">
              <span class="user-name"><?= e($user['name'] ?? 'Usuário') ?></span>
              <span class="user-role"><?= e($user['role'] ?? 'SOLICITANTE') ?></span>
            </div>
            <a href="<?= BASE_URL ?>/logout.php" class="btn-logout" data-confirm="Deseja realmente sair do sistema?">Sair</a>
          </li>
        <?php else: ?>
          <li><a href="<?= BASE_URL ?>/index.php" class="nav-link">Início</a></li>
          <li><a href="<?= BASE_URL ?>/login.php" class="nav-link">Entrar</a></li>
          <li><a href="<?= BASE_URL ?>/cadastro.php" class="nav-link btn btn-sm btn-primary" style="color:#fff;">Criar Conta</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<main class="main-content">
  <?= renderFlashMessages() ?>
