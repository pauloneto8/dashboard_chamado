<?php
/**
 * Arquivo de configuração principal da aplicação.
 * Centraliza constantes e configurações de ambiente.
 */

// Exibe erros apenas em desenvolvimento (nunca em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fuso horário padrão
date_default_timezone_set('America/Recife');

// Caminhos base da aplicação
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', APP_PATH . '/Views');

// URL base (ajustar conforme o ambiente)
define('BASE_URL', 'https://api.rosamaster.com/dashboard_chamados/public');

// Configurações do banco de dados MySQL
define('DB_HOST', '192.168.0.6');
define('DB_NAME', 'dashboard_chamado');
define('DB_USER', 'analista.master');
define('DB_PASS', '|saGZjhzbYL6prs?');
define('DB_CHARSET', 'utf8mb4');

// API GLPI (v2.2 usa OAuth2 Bearer; legacy usa App-Token + user_token)
define('GLPI_API_URL', 'https://suporte.rosamaster.com/api.php/v2.2');
define('GLPI_APP_TOKEN', 'wrv5ShpruRuBdo0VR2e8q5DR6WMgzhhkVn5qVj8a');
define('GLPI_USER_TOKEN', 'YOUJyEuB7CZic0HyjMcgPcEFSp9nsYOyhahHoq3Q');
// Se usar OAuth2, defina o access_token (obtido via Password Grant no GLPI):
define('GLPI_OAUTH_ACCESS_TOKEN', '');

// Chave para sessão (gerar uma chave segura em produção)
define('SESSION_SECRET', 'alterar_em_producao_' . bin2hex(random_bytes(8)));

// Bitrix24 OAuth2 (registrar app em: Desenvolvimento > Aplicações personalizadas)
define('BITRIX24_DOMAIN', 'portal.bitrix24.com'); // ou seu portal, ex: empresa.bitrix24.com.br
define('BITRIX24_CLIENT_ID', '');
define('BITRIX24_CLIENT_SECRET', '');
define('BITRIX24_REDIRECT_URI', BASE_URL . '/auth/callback');
