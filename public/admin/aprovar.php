<?php
/**
 * SisReserva IFRO — Aprovação de Reserva (Admin)
 * Revalidação de regras de negócio, prevenção de conflitos e persistência do revisor
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
if ($id <= 0) {
    setFlash('danger', 'Identificador de reserva inválido.');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;
}

$admin = currentUser();

try {
    $pdo = getDBConnection();

    // 1. Busca a reserva e os dados do recurso
    $stmt = $pdo->prepare(
        'SELECT r.id, r.resource_id, r.start_time, r.end_time, r.status,
                res.name as resource_name, res.status as resource_status
         FROM reservations r
         JOIN resources res ON r.resource_id = res.id
         WHERE r.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        setFlash('danger', 'Reserva não encontrada no sistema.');
        header('Location: ' . BASE_URL . '/admin/reservas.php');
        exit;
    }

    // 2. Não aprovar se o recurso estiver em MANUTENCAO ou INATIVO
    if ($reservation['resource_status'] === 'MANUTENCAO') {
        setFlash('danger', 'Não é possível aprovar: O recurso "' . e($reservation['resource_name']) . '" está em MANUTENÇÃO.');
        header('Location: ' . BASE_URL . '/admin/reservas.php');
        exit;
    }

    if ($reservation['resource_status'] === 'INATIVO') {
        setFlash('danger', 'Não é possível aprovar: O recurso "' . e($reservation['resource_name']) . '" está INATIVO.');
        header('Location: ' . BASE_URL . '/admin/reservas.php');
        exit;
    }

    // 3. RN08: Não permitir aprovação de uma reserva que esteja no passado
    if (strtotime($reservation['start_time']) <= time()) {
        setFlash('danger', 'Não é possível aprovar: O horário desta reserva já ocorreu no passado.');
        header('Location: ' . BASE_URL . '/admin/reservas.php');
        exit;
    }

    // 4. Re-verificação de conflito contra reservas já APROVADAS no mesmo intervalo
    $stmtConflict = $pdo->prepare(
        'SELECT id, start_time, end_time 
         FROM reservations 
         WHERE resource_id = :resource_id 
           AND status = "APROVADA"
           AND id != :current_id
           AND start_time < :end_time 
           AND end_time > :start_time
         LIMIT 1'
    );
    $stmtConflict->execute([
        ':resource_id' => $reservation['resource_id'],
        ':current_id'  => $id,
        ':start_time'  => $reservation['start_time'],
        ':end_time'    => $reservation['end_time'],
    ]);

    $existingApproved = $stmtConflict->fetch();
    if ($existingApproved) {
        setFlash('danger', 'Conflito detectado: Já existe outra reserva APROVADA para este recurso no mesmo horário.');
        header('Location: ' . BASE_URL . '/admin/reservas.php');
        exit;
    }

    // 5. Atualiza o status para APROVADA e registra o administrador responsável
    $stmtUpdate = $pdo->prepare(
        'UPDATE reservations 
         SET status = "APROVADA", reviewed_by_id = :admin_id, rejection_reason = NULL 
         WHERE id = :id'
    );
    $stmtUpdate->execute([
        ':admin_id' => $admin['id'],
        ':id'       => $id,
    ]);

    setFlash('success', 'Reserva #' . $id . ' aprovada com sucesso!');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;

} catch (Exception $e) {
    error_log('[SisReserva Aprovar] Erro: ' . $e->getMessage());
    setFlash('danger', 'Ocorreu um erro ao aprovar a reserva.');
    header('Location: ' . BASE_URL . '/admin/reservas.php');
    exit;
}
