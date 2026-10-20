<?php
/**
 * SisReserva IFRO — Cancelamento de Reservas
 * Verificação de posse, impedimento de cancelamento no passado e CSRF
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/reservas/minhas.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($token)) {
    setFlash('danger', 'Token de segurança inválido. Tente novamente.');
    header('Location: ' . BASE_URL . '/reservas/minhas.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$user = currentUser();

if ($id <= 0) {
    setFlash('danger', 'Identificador de reserva inválido.');
    header('Location: ' . BASE_URL . '/reservas/minhas.php');
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id, user_id, start_time, status FROM reservations WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        setFlash('danger', 'Reserva não encontrada no sistema.');
        header('Location: ' . BASE_URL . '/reservas/minhas.php');
        exit;
    }

    // Validação de Permissão: Apenas o próprio autor ou ADMIN podem cancelar
    if (!isAdmin() && (int)$reservation['user_id'] !== (int)$user['id']) {
        setFlash('danger', 'Você não possui permissão para cancelar reservas de outros usuários.');
        header('Location: ' . BASE_URL . '/reservas/minhas.php');
        exit;
    }

    // RN09: Não permitir cancelamento de uma reserva que esteja no passado
    if (strtotime($reservation['start_time']) <= time()) {
        setFlash('danger', 'Não é permitido cancelar uma reserva que já iniciou ou ocorreu no passado.');
        header('Location: ' . BASE_URL . '/reservas/minhas.php');
        exit;
    }

    if ($reservation['status'] === 'CANCELADA') {
        setFlash('info', 'Esta reserva já estava cancelada.');
        header('Location: ' . BASE_URL . '/reservas/minhas.php');
        exit;
    }

    // Atualiza status para CANCELADA
    $updateStmt = $pdo->prepare('UPDATE reservations SET status = "CANCELADA" WHERE id = :id');
    $updateStmt->execute([':id' => $id]);

    setFlash('success', 'Reserva #' . $id . ' cancelada com sucesso.');

    // Redireciona conforme a origem
    if (isAdmin() && !empty($_POST['redirect_admin'])) {
        header('Location: ' . BASE_URL . '/admin/reservas.php');
    } else {
        header('Location: ' . BASE_URL . '/reservas/minhas.php');
    }
    exit;

} catch (Exception $e) {
    error_log('[SisReserva Cancelar] Erro: ' . $e->getMessage());
    setFlash('danger', 'Ocorreu um erro ao cancelar a reserva.');
    header('Location: ' . BASE_URL . '/reservas/minhas.php');
    exit;
}
