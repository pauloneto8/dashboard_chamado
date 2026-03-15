<?php
/**
 * Front Controller - Ponto de entrada único da aplicação.
 * Todas as requisições passam por aqui e são encaminhadas ao roteador.
 */

// Carrega configurações e autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// Inicia a sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Encaminha para o roteador da aplicação
$app = new App\Core\App();
$app->executar();
