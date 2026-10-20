<?php
/**
 * SisReserva IFRO — Encerramento de Sessão (Logout)
 * Destrói cookies e dados de sessão de forma segura
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

logoutUser();

// Inicia nova sessão limpa apenas para transportar a mensagem de feedback
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
setFlash('info', 'Você saiu do sistema com segurança.');

header('Location: ' . BASE_URL . '/login.php');
exit;
