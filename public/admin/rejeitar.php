<?php
/**
 * SisReserva IFRO — Rejeição de Reserva com Justificativa Obrigatória (Admin)
 * Exige justificativa com mínimo de 10 caracteres (Seção 13 e Cenário 5)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($token)) {
    setFlash('danger', 'Token de segurança inválido. Tente novamente.');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$reason = trim((string)($_POST['rejection_reason'] ?? ''));

if ($id <= 0) {
    setFlash('danger', 'Identificador de reserva inválido.');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;
}

// Seção 13: O motivo deve possuir no mínimo 10 caracteres
if (mb_strlen($reason) < RN_MIN_REJECTION_REASON_LEN) {
    setFlash('danger', 'O motivo da rejeição deve possuir pelo menos 10 caracteres explicativos.');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;
}

$admin = currentUser();

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare('SELECT id, status FROM reservations WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        setFlash('danger', 'Reserva não encontrada no sistema.');
        header('Location: ' . BASE_URL . '/admin/reservas.php');
        exit;
    }

    // Atualiza status para RECUSADA com justificativa e revisor
    $updateStmt = $pdo->prepare(
        'UPDATE reservations 
         SET status = "RECUSADA", rejection_reason = :reason, reviewed_by_id = :admin_id 
         WHERE id = :id'
    );
    $updateStmt->execute([
        ':reason'   => $reason,
        ':admin_id' => $admin['id'],
        ':id'       => $id,
    ]);

    setFlash('success', 'Reserva #' . $id . ' rejeitada com sucesso. O solicitante visualizará o motivo informado.');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;

} catch (Exception $e) {
    error_log('[SisReserva Rejeitar] Erro: ' . $e->getMessage());
    setFlash('danger', 'Ocorreu um erro ao registrar a rejeição da reserva.');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;
}
