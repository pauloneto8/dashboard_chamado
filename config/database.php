<?php
/**
 * Configuração e conexão PDO com MySQL.
 * Utiliza prepared statements para segurança.
 */

require_once CONFIG_PATH . '/config.php';

/**
 * Retorna uma instância PDO da conexão com o banco.
 * @return PDO
 * @throws PDOException
 */
function getConexao(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );
        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
    }

    return $pdo;
}
