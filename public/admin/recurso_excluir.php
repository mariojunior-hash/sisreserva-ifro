<?php
/**
 * SisReserva IFRO — Exclusão de Recurso (Admin)
 * Remove o recurso do banco de dados com verificação CSRF e tratamento de integridade
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/flash.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($token)) {
    setFlash('danger', 'Token de segurança inválido. Tente novamente.');
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Identificador de recurso inválido.');
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('DELETE FROM resources WHERE id = :id');
    $stmt->execute([':id' => $id]);

    setFlash('success', 'Recurso excluído com sucesso.');
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;

} catch (Exception $e) {
    error_log('[SisReserva Excluir Recurso] Erro: ' . $e->getMessage());
    setFlash('danger', 'Não foi possível excluir o recurso selecionado.');
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;
}
