<?php
/**
 * Container simples para dependências da aplicação.
 * Fornece instâncias de conexão PDO, GlpiApiService e models.
 */

namespace App\Core;

use App\Models\ChamadoModel;
use App\Models\UsuarioModel;
use App\Services\GlpiApiService;
use PDO;

class Container
{
    private static ?PDO $pdo = null;
    private static ?GlpiApiService $glpi = null;
    private static ?ChamadoModel $chamadoModel = null;
    private static ?UsuarioModel $usuarioModel = null;

    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = getConexao();
        }
        return self::$pdo;
    }

    public static function getGlpiApi(): GlpiApiService
    {
        if (self::$glpi === null) {
            self::$glpi = new GlpiApiService();
        }
        return self::$glpi;
    }

    public static function getChamadoModel(): ChamadoModel
    {
        if (self::$chamadoModel === null) {
            self::$chamadoModel = new ChamadoModel(self::getPdo(), self::getGlpiApi());
        }
        return self::$chamadoModel;
    }

    public static function getUsuarioModel(): UsuarioModel
    {
        if (self::$usuarioModel === null) {
            self::$usuarioModel = new UsuarioModel(self::getPdo());
        }
        return self::$usuarioModel;
    }
}
