<?php
/**
 * SisReserva IFRO — Processamento e Criação de Reserva
 * Validação rigorosa no servidor, verificação algorítmica de conflitos e transação segura
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/reservas/nova.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($token)) {
    setFlash('danger', 'Token de segurança inválido ou expirado. Tente novamente.');
    header('Location: ' . BASE_URL . '/reservas/nova.php');
    exit;
}

$user = currentUser();
$resourceId = (int)($_POST['resource_id'] ?? 0);
$date = trim((string)($_POST['date'] ?? ''));
$startTimeStr = trim((string)($_POST['start_time'] ?? ''));
$endTimeStr = trim((string)($_POST['end_time'] ?? ''));
$purpose = trim((string)($_POST['purpose'] ?? ''));

// Validação dos campos obrigatórios
if ($resourceId <= 0 || empty($date) || empty($startTimeStr) || empty($endTimeStr) || empty($purpose)) {
    setFlash('danger', 'Por favor, preencha todos os campos obrigatórios.');
    header('Location: ' . BASE_URL . '/reservas/nova.php' . ($resourceId > 0 ? '?recurso_id=' . $resourceId : ''));
    exit;
}

$startDatetimeStr = $date . ' ' . $startTimeStr . ':00';
$endDatetimeStr = $date . ' ' . $endTimeStr . ':00';

$startTs = strtotime($startDatetimeStr);
$endTs = strtotime($endDatetimeStr);
$nowTs = time();

if ($startTs === false || $endTs === false) {
    setFlash('danger', 'Formato de data ou horário inválido.');
    header('Location: ' . BASE_URL . '/reservas/nova.php');
    exit;
}

// 1. Horário inicial deve ser estritamente anterior ao final
if ($startTs >= $endTs) {
    setFlash('danger', 'O horário final deve ser posterior ao horário inicial.');
    header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
    exit;
}

// 2. RN07: Não permitir reservas no passado
if ($startTs <= $nowTs) {
    setFlash('danger', 'Não é permitido criar solicitações de reserva para horários no passado.');
    header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
    exit;
}

// 3. RN01: Antecedência mínima de 2 horas
$minAdvanceSeconds = RN_MIN_ADVANCE_HOURS * 3600;
if (($startTs - $nowTs) < $minAdvanceSeconds) {
    setFlash('danger', 'A reserva deve ser solicitada com pelo menos 2 horas de antecedência.');
    header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
    exit;
}

// 4. RN02: Antecedência máxima de 30 dias
$maxAdvanceSeconds = RN_MAX_ADVANCE_DAYS * 86400;
if (($startTs - $nowTs) > $maxAdvanceSeconds) {
    setFlash('danger', 'A reserva pode ser feita no máximo 30 dias no futuro.');
    header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
    exit;
}

// 5. RN03: Duração mínima de 45 minutos
$durationMinutes = ($endTs - $startTs) / 60;
if ($durationMinutes < RN_MIN_DURATION_MINUTES) {
    setFlash('danger', 'A duração mínima da reserva é de 45 minutos (1 hora-aula).');
    header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
    exit;
}

// 6. RN04: Limite de 4 horas contínuas por turno
$maxDurationMinutes = RN_MAX_CONTINUOUS_HOURS * 60;
if ($durationMinutes > $maxDurationMinutes) {
    setFlash('danger', 'O solicitante pode reservar no máximo 4 horas contínuas por turno.');
    header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
    exit;
}

try {
    $pdo = getDBConnection();

    // 7. RN05 e RN06: Verificar se o recurso existe e está ATIVO
    $stmt = $pdo->prepare('SELECT id, name, status FROM resources WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $resourceId]);
    $resource = $stmt->fetch();

    if (!$resource) {
        setFlash('danger', 'O recurso selecionado não foi encontrado no sistema.');
        header('Location: ' . BASE_URL . '/reservas/nova.php');
        exit;
    }

    if ($resource['status'] === 'MANUTENCAO') {
        setFlash('danger', 'O recurso "' . e($resource['name']) . '" está em MANUTENÇÃO e não pode ser reservado.');
        header('Location: ' . BASE_URL . '/reservas/nova.php');
        exit;
    }

    if ($resource['status'] === 'INATIVO') {
        setFlash('danger', 'O recurso "' . e($resource['name']) . '" está INATIVO e indisponível para reservas.');
        header('Location: ' . BASE_URL . '/reservas/nova.php');
        exit;
    }

    // 8. Seção 10 e 11: Verificação atômica de conflito temporal com Transação InnoDB
    $pdo->beginTransaction();

    // Regra matemática de conflito:
    // MAX(existing_start, new_start) < MIN(existing_end, new_end)
    // Equivalente em SQL: start_time < :new_end AND end_time > :new_start
    $conflictSql = '
        SELECT id, start_time, end_time, status 
        FROM reservations 
        WHERE resource_id = :resource_id 
          AND status IN ("PENDENTE", "APROVADA")
          AND start_time < :new_end 
          AND end_time > :new_start
        FOR UPDATE
    ';

    $stmt = $pdo->prepare($conflictSql);
    $stmt->execute([
        ':resource_id' => $resourceId,
        ':new_start'   => $startDatetimeStr,
        ':new_end'     => $endDatetimeStr,
    ]);

    $conflicts = $stmt->fetchAll();

    if (!empty($conflicts)) {
        $pdo->rollBack();
        $conflict = $conflicts[0];
        $msg = sprintf(
            'Conflito de Horário! Já existe uma reserva %s para este recurso entre %s e %s.',
            e($conflict['status']),
            formatTimeBr($conflict['start_time']),
            formatTimeBr($conflict['end_time'])
        );
        setFlash('danger', $msg);
        header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
        exit;
    }

    // 9. RN10: Insere a nova reserva com status PENDENTE
    $insertSql = '
        INSERT INTO reservations (user_id, resource_id, start_time, end_time, purpose, status)
        VALUES (:user_id, :resource_id, :start_time, :end_time, :purpose, "PENDENTE")
    ';
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        ':user_id'     => $user['id'],
        ':resource_id' => $resourceId,
        ':start_time'  => $startDatetimeStr,
        ':end_time'    => $endDatetimeStr,
        ':purpose'     => $purpose,
    ]);

    $pdo->commit();

    setFlash('success', 'Solicitação de reserva criada com sucesso! Status atual: PENDENTE (aguardando moderação da coordenação).');
    header('Location: ' . BASE_URL . '/reservas/minhas.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[SisReserva Criar Reserva] Erro: ' . $e->getMessage());
    setFlash('danger', 'Ocorreu um erro ao processar sua solicitação de reserva. Tente novamente mais tarde.');
    header('Location: ' . BASE_URL . '/reservas/nova.php?recurso_id=' . $resourceId);
    exit;
}
