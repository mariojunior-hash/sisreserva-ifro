<?php
/**
 * SisReserva IFRO — Funções Auxiliares e Sanitização
 * Prevenção de XSS, Proteção CSRF e Utilitários de Formatação
 */

declare(strict_types=1);

/**
 * Escapa strings para saída segura em HTML, prevenindo ataques XSS.
 *
 * @param mixed $value
 * @return string
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Retorna uma URL formatada a partir da BASE_URL ou ASSETS_URL do sistema.
 *
 * @param string $path Caminho relativo (ex: 'login.php' ou 'assets/css/style.css')
 * @return string
 */
function url(string $path = ''): string
{
    $cleanPath = ltrim($path, '/');
    if (str_starts_with($cleanPath, 'assets/')) {
        return rtrim(ASSETS_URL, '/') . '/' . substr($cleanPath, 7);
    }
    if (str_starts_with($cleanPath, 'public/')) {
        return rtrim(BASE_URL, '/') . '/' . substr($cleanPath, 7);
    }
    return rtrim(BASE_URL, '/') . '/' . $cleanPath;
}

/**
 * Gera ou recupera o token CSRF atual da sessão.
 *
 * @return string
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retorna o campo de formulário HTML com o token CSRF embutido.
 *
 * @return string
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(getCsrfToken()) . '">';
}

/**
 * Valida o token CSRF recebido na requisição contra o token da sessão.
 *
 * @param string|null $token
 * @return bool
 */
function verifyCsrfToken(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Normaliza e-mail: remove espaços e converte para minúsculas.
 *
 * @param string $email
 * @return string
 */
function normalizeEmail(string $email): string
{
    return strtolower(trim($email));
}

/**
 * Formata data e hora para o padrão brasileiro (dd/mm/aaaa às hh:mm).
 *
 * @param string|null $datetime
 * @return string
 */
function formatDateTimeBr(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '-';
    }
    return date('d/m/Y \à\s H:i', $ts);
}

/**
 * Formata somente a data (dd/mm/aaaa).
 *
 * @param string|null $datetime
 * @return string
 */
function formatDateBr(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '-';
    }
    return date('d/m/Y', $ts);
}

/**
 * Formata somente a hora (hh:mm).
 *
 * @param string|null $datetime
 * @return string
 */
function formatTimeBr(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '-';
    }
    return date('H:i', $ts);
}

/**
 * Retorna o badge HTML estilizado para status de reserva.
 *
 * @param string $status
 * @return string
 */
function renderReservationBadge(string $status): string
{
    $status = strtoupper(trim($status));
    $classes = [
        'PENDENTE'  => 'badge badge-warning',
        'APROVADA'  => 'badge badge-success',
        'RECUSADA'  => 'badge badge-danger',
        'CANCELADA' => 'badge badge-muted',
    ];
    $cls = $classes[$status] ?? 'badge badge-secondary';
    return '<span class="' . $cls . '">' . e($status) . '</span>';
}

/**
 * Retorna o badge HTML estilizado para status de recurso.
 *
 * @param string $status
 * @return string
 */
function renderResourceBadge(string $status): string
{
    $status = strtoupper(trim($status));
    $classes = [
        'ATIVO'      => 'badge badge-success',
        'MANUTENCAO' => 'badge badge-warning',
        'INATIVO'    => 'badge badge-danger',
    ];
    $cls = $classes[$status] ?? 'badge badge-secondary';
    return '<span class="' . $cls . '">' . e($status) . '</span>';
}
