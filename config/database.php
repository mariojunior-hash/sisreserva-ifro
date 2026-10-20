<?php
/**
 * SisReserva IFRO — Configuração e Conexão com Banco de Dados MySQL/MariaDB
 * Utiliza PDO com Prepared Statements e tratamento seguro de exceções.
 */

declare(strict_types=1);

// Permite sobreposição por arquivo local não versionado (database.local.php) se existir
if (file_exists(__DIR__ . '/database.local.php')) {
    require_once __DIR__ . '/database.local.php';
}

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}

if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'sisreserva');
}

if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}

if (!defined('DB_PASS')) {
    // Fallback padrão do XAMPP (sem senha). Para senhas personalizadas, configure config/database.local.php
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
}

if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

/**
 * Retorna uma instância singleton de conexão PDO com o MySQL/MariaDB.
 *
 * @return PDO
 * @throws RuntimeException Caso ocorra falha de conexão sem expor credenciais.
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Registra o detalhe técnico internamente no log de erros do servidor
        error_log('[SisReserva DB Error] Falha de conexão: ' . $e->getMessage());

        // Mensagem segura ao usuário sem expor caminhos, senhas ou SQLSTATE
        throw new RuntimeException(
            'Não foi possível estabelecer conexão com o banco de dados. ' .
            'Verifique se o serviço MySQL está ativo e se as credenciais em config/database.php estão corretas.'
        );
    }
}
