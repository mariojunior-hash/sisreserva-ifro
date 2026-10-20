<?php
/**
 * SisReserva IFRO — Redirecionamento Inicial da Raiz
 * Redireciona o acesso de /sisreserva/ para /sisreserva/public/index.php
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

header('Location: ' . BASE_URL . '/index.php');
exit;
