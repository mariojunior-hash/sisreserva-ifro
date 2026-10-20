<?php
/**
 * SisReserva IFRO — Salvar Recurso (Admin)
 * Processa a criação e atualização de recursos via PDO com Prepared Statements
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
$name = trim((string)($_POST['name'] ?? ''));
$category = trim((string)($_POST['category'] ?? ''));
$capacityStr = trim((string)($_POST['capacity'] ?? ''));
$capacity = $capacityStr !== '' ? (int)$capacityStr : null;
$location = trim((string)($_POST['location'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$status = strtoupper(trim((string)($_POST['status'] ?? 'ATIVO')));

if (!in_array($status, ['ATIVO', 'MANUTENCAO', 'INATIVO'], true)) {
    $status = 'ATIVO';
}

if (empty($name) || empty($category) || empty($location)) {
    setFlash('danger', 'Preencha todos os campos obrigatórios (Nome, Categoria e Localização).');
    header('Location: ' . ($id > 0 ? BASE_URL . '/admin/recurso_editar.php?id=' . $id : BASE_URL . '/admin/recurso_novo.php'));
    exit;
}

try {
    $pdo = getDBConnection();

    if ($id > 0) {
        // Atualização de recurso existente
        $stmt = $pdo->prepare(
            'UPDATE resources 
             SET name = :name, category = :category, capacity = :capacity, 
                 location = :location, description = :description, status = :status
             WHERE id = :id'
        );
        $stmt->execute([
            ':id'          => $id,
            ':name'        => $name,
            ':category'    => $category,
            ':capacity'    => $capacity,
            ':location'    => $location,
            ':description' => $description !== '' ? $description : null,
            ':status'      => $status,
        ]);
        setFlash('success', 'Recurso "' . e($name) . '" atualizado com sucesso.');
    } else {
        // Inserção de novo recurso
        $stmt = $pdo->prepare(
            'INSERT INTO resources (name, category, capacity, location, description, status)
             VALUES (:name, :category, :capacity, :location, :description, :status)'
        );
        $stmt->execute([
            ':name'        => $name,
            ':category'    => $category,
            ':capacity'    => $capacity,
            ':location'    => $location,
            ':description' => $description !== '' ? $description : null,
            ':status'      => $status,
        ]);
        setFlash('success', 'Novo recurso "' . e($name) . '" cadastrado com sucesso.');
    }

    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;

} catch (Exception $e) {
    error_log('[SisReserva Salvar Recurso] Erro: ' . $e->getMessage());
    setFlash('danger', 'Ocorreu um erro ao salvar o recurso.');
    header('Location: ' . BASE_URL . '/admin/recursos.php');
    exit;
}
