<?php
/**
 * SisReserva IFRO — Sistema de Mensagens Flash
 * Gerencia mensagens temporárias em sessão para feedback ao usuário
 */

declare(strict_types=1);

/**
 * Define uma mensagem flash na sessão.
 *
 * @param string $type Tipo da mensagem: 'success', 'danger', 'warning', 'info'
 * @param string $message Texto da mensagem
 * @return void
 */
function setFlash(string $type, string $message): void
{
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Verifica se existem mensagens flash registradas.
 *
 * @return bool
 */
function hasFlash(): bool
{
    return !empty($_SESSION['flash']);
}

/**
 * Renderiza e consome todas as mensagens flash pendentes na sessão.
 *
 * @return string Código HTML dos alertas
 */
function renderFlashMessages(): string
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return '';
    }

    $messages = $_SESSION['flash'];
    unset($_SESSION['flash']); // Consome as mensagens para não reaparecerem no reload

    $html = '<div class="flash-container">';
    foreach ($messages as $flash) {
        $type = in_array($flash['type'], ['success', 'danger', 'warning', 'info'], true)
            ? $flash['type']
            : 'info';

        $iconMap = [
            'success' => '✓',
            'danger'  => '✕',
            'warning' => '⚠',
            'info'    => 'ℹ',
        ];
        $icon = $iconMap[$type] ?? 'ℹ';

        $html .= sprintf(
            '<div class="alert alert-%s" role="alert">' .
            '  <span class="alert-icon">%s</span>' .
            '  <div class="alert-content">%s</div>' .
            '  <button type="button" class="alert-close" onclick="this.parentElement.remove();" aria-label="Fechar">&times;</button>' .
            '</div>',
            e($type),
            $icon,
            e($flash['message'])
        );
    }
    $html .= '</div>';

    return $html;
}
