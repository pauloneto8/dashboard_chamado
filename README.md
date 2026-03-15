# Dashboard de Chamados (Kanban)

Dashboard em formato Kanban para gestão à vista do time de suporte. Chamados consumidos do GLPI via API.

## Requisitos

- PHP 7.4+
- MySQL 5.7+ ou MariaDB
- Composer
- Servidor web (Apache com mod_rewrite) ou XAMPP

## Instalação

1. **Dependências**
   ```bash
   composer install
   ```

2. **Banco de dados**
   - Crie o banco: `CREATE DATABASE dashboard_chamado CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
   - Importe o schema: `mysql -u root dashboard_chamado < database/schema.sql`
   - Ajuste usuário/senha em `config/config.php` (DB_USER, DB_PASS).

3. **Configuração**
   - Edite `config/config.php`: defina `BASE_URL` conforme sua URL (ex: `http://localhost/dashboard_chamado/public`).
   - Para integrar a API GLPI: a API v2.2 usa **OAuth2**. Configure client_id, client_secret e usuário/senha no GLPI (Setup > OAuth Clients) e depois implemente a obtenção do token em `App\Services\GlpiApiService` (Bearer token).

## Estrutura do projeto

- `app/Controllers/` – Controllers MVC
- `app/Models/` – Models (Chamado, etc.)
- `app/Views/` – Views (layout e páginas)
- `app/Services/` – Serviços (API GLPI)
- `app/Core/` – Núcleo (App, Container)
- `config/` – Configurações
- `database/` – Scripts SQL
- `public/` – Ponto de entrada (index.php) e assets

## Acesso

Abra no navegador a URL configurada em `BASE_URL`, por exemplo:

- `http://localhost/dashboard_chamado/public`

O Kanban exibe as etapas: **Novo**, **Programado**, **Pendente**, **Solucionado**.

## Próximos passos

- Autenticação Bitrix24
- Liberação de permissões pelo primeiro usuário (admin)
- Integração OAuth2 com a API GLPI para listar/atualizar chamados
- Arrastar e soltar (drag and drop) entre colunas do Kanban
