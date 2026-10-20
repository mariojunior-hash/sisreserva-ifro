<?php
/**
 * SisReserva IFRO — Configuração Global do Sistema
 * Gerenciamento de Sessão Segura, Constantes e Regras de Negócio
 */

declare(strict_types=1);

// Fuso horário oficial de Rondônia (Porto Velho - UTC-4)
date_default_timezone_set('America/Porto_Velho');

// ------------------------------------------------------------
// 1. CONSTANTES DO SISTEMA
// ------------------------------------------------------------
if (!defined('APP_NAME')) {
    define('APP_NAME', 'SisReserva IFRO');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}
if (!defined('APP_INSTITUTION')) {
    define('APP_INSTITUTION', 'IFRO - Campus Porto Velho Calama');
}

// Caminho absoluto da raiz do projeto no sistema de arquivos
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// ------------------------------------------------------------
// 2. DEFINIÇÃO DA URL BASE DO SISTEMA
// Conforme especificação: BASE_URL aponta para /sisreserva/public
// ------------------------------------------------------------
if (!defined('BASE_URL')) {
    define('BASE_URL', '/sisreserva/public');
}

if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', '/sisreserva/assets');
}

// ------------------------------------------------------------
// 3. REGRAS DE NEGÓCIO OFICIAIS (RN01 a RN13)
// ------------------------------------------------------------
const RN_MIN_ADVANCE_HOURS         = 2;   // RN01: Mínimo 2 horas de antecedência
const RN_MAX_ADVANCE_DAYS          = 30;  // RN02: Máximo 30 dias no futuro
const RN_MIN_DURATION_MINUTES      = 45;  // RN03: Duração mínima de 45 minutos (1 aula)
const RN_MAX_CONTINUOUS_HOURS      = 4;   // RN04: Máximo 4 horas contínuas por turno
const RN_MIN_REJECTION_REASON_LEN  = 10;  // Rejeição exige justificativa mínima de 10 caracteres

// ------------------------------------------------------------
// 4. CONFIGURAÇÃO DE SESSÃO SEGURA
// Cookie HttpOnly, SameSite=Lax, Secure em HTTPS
// ------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    session_name('SISRESERVA_SESSION');

    session_set_cookie_params([
        'lifetime' => 0,          // Expira ao fechar o navegador
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,  // Habilita automaticamente se rodar em HTTPS
        'httponly' => true,       // Impede acesso aos cookies via JavaScript (XSS)
        'samesite' => 'Lax',      // Previne ataques CSRF
    ]);

    session_start();
}

// Carrega as configurações de banco de dados
require_once __DIR__ . '/database.php';
