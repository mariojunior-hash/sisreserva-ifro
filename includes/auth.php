<?php
/**
 * SisReserva IFRO — Módulo de Autenticação e Controle de Acesso (RBAC)
 * Proteção de Rotas no Servidor, Gestão de Sessão e Verificação de Permissões
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash.php';

/**
 * Verifica se há um usuário autenticado na sessão atual.
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user']['id']);
}

/**
 * Retorna os dados do usuário atualmente autenticado ou null.
 *
 * @return array<string, mixed>|null
 */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Verifica se o usuário autenticado possui o perfil de Administrador (ADMIN).
 *
 * @return bool
 */
function isAdmin(): bool
{
    return isLoggedIn() && (($_SESSION['user']['role'] ?? '') === 'ADMIN');
}

/**
 * Verifica se o usuário autenticado possui o perfil de Solicitante.
 *
 * @return bool
 */
function isSolicitante(): bool
{
    return isLoggedIn() && (($_SESSION['user']['role'] ?? '') === 'SOLICITANTE');
}

/**
 * Exige que o usuário esteja autenticado para acessar a página.
 * Caso contrário, redireciona para a página de login com mensagem de aviso.
 *
 * @return void
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'É necessário fazer login para acessar esta página.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Exige privilégios de Administrador (ADMIN).
 * Se não autenticado, redireciona para login.
 * Se autenticado como SOLICITANTE, nega acesso e redireciona para o dashboard com mensagem.
 *
 * @return void
 */
function requireAdmin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'Acesso restrito. Faça login como administrador.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    if (!isAdmin()) {
        setFlash('danger', 'Você não possui permissão para acessar esta área administrativa.');
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Registra o login do usuário na sessão com regeneração segura do ID da sessão.
 *
 * @param array<string, mixed> $userData
 * @return void
 */
function loginUser(array $userData): void
{
    // Previne Session Fixation regenerando o identificador da sessão
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'        => (int)$userData['id'],
        'name'      => $userData['name'],
        'email'     => $userData['email'],
        'matricula' => $userData['matricula'],
        'role'      => $userData['role'],
    ];

    // Renova o token CSRF no login
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Encerra a sessão do usuário de forma segura, removendo dados e cookie de sessão.
 *
 * @return void
 */
function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
