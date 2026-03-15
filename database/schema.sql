-- Schema inicial do Dashboard de Chamados
-- Banco: dashboard_chamado (criar manualmente: CREATE DATABASE dashboard_chamado CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tabela de usuários (vinculados ao Bitrix24; primeiro usuário é admin)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bitrix_id VARCHAR(64) NOT NULL UNIQUE COMMENT 'ID do usuário no Bitrix24',
    email VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    admin TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = primeiro usuário/admin que libera os demais',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários autenticados via Bitrix24';

-- Permissões liberadas pelo admin (quem pode acessar o dashboard)
CREATE TABLE IF NOT EXISTS permissoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    liberado_por INT UNSIGNED NOT NULL COMMENT 'Admin que liberou',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuario (usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (liberado_por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cache/local dos chamados vindos do GLPI (opcional: para exibir mesmo offline)
CREATE TABLE IF NOT EXISTS chamados (
    id INT UNSIGNED NOT NULL COMMENT 'ID do ticket no GLPI',
    glpi_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(500) NOT NULL,
    etapa ENUM('Novo','Programado','Pendente','Solucionado') NOT NULL DEFAULT 'Novo',
    descricao TEXT,
    solicitante VARCHAR(255),
    data_abertura DATETIME,
    data_atualizacao DATETIME,
    sincronizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_etapa (etapa),
    INDEX idx_sincronizado (sincronizado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache dos chamados da API GLPI';

SET FOREIGN_KEY_CHECKS = 1;
