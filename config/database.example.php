<?php
/**
 * SisReserva IFRO — Exemplo de Configuração de Banco de Dados Local
 *
 * INSTRUÇÕES PARA IMPLANTAÇÃO:
 * 1. Copie este arquivo para 'config/database.local.php':
 *    cp config/database.example.php config/database.local.php
 * 2. Altere os valores abaixo conforme seu ambiente local (XAMPP) ou provedor web
 * 3. O arquivo 'database.local.php' é ignorado pelo Git e NÃO será versionado.
 */

declare(strict_types=1);

// Servidor do banco de dados (ex: 'localhost', '127.0.0.1' ou 'sql123.provedor.com')
define('DB_HOST', 'localhost');

// Porta do serviço MySQL / MariaDB (padrão XAMPP: '3306')
define('DB_PORT', '3306');

// Nome da base de dados criada (ex: 'sisreserva' ou 'epiz_123456_sisreserva')
define('DB_NAME', 'sisreserva');

// Usuário com permissão na base de dados (ex: 'root' ou 'epiz_123456')
define('DB_USER', 'root');

// Senha de acesso ao banco (deixe vazio '' no XAMPP padrão)
define('DB_PASS', '');

// Conjunto de caracteres recomendado para suporte completo a caracteres em português
define('DB_CHARSET', 'utf8mb4');
