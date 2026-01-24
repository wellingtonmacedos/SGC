# Guia de Instalação - SGC (Sistema de Gestão de Cursos)

Este guia descreve como instalar o SGC em um servidor de hospedagem (cPanel, Plesk, etc.).

## 1. Pré-requisitos
- PHP 8.1 ou superior
- MySQL 5.7 ou MariaDB 10.2+
- Extensões PHP: `pdo`, `pdo_mysql`, `mbstring`, `gd`
- Acesso ao gerenciador de arquivos e banco de dados da hospedagem

## 2. Banco de Dados
1. Acesse o painel da sua hospedagem e crie um novo banco de dados MySQL (ex: `inscricoes_db`).
2. Crie um usuário para este banco e anote a senha.
3. Abra o **phpMyAdmin** (ou ferramenta similar).
4. Selecione o banco de dados criado.
5. Importe o arquivo `database_full.sql` que está na raiz do projeto.
   - Este arquivo já contém a estrutura completa e o usuário Super Admin inicial.

## 3. Upload de Arquivos
1. Faça o upload de **todos os arquivos e pastas** do projeto para a pasta pública do seu subdomínio (geralmente `public_html` ou uma pasta com o nome do subdomínio).
   - Exemplo: `/public_html/inscricoes-camara/` ou a raiz do subdomínio.

## 4. Configuração
1. Renomeie o arquivo `config.example.php` para `config.php`.
2. Edite o `config.php` e altere as seguintes linhas com seus dados:

```php
define('DB_HOST', 'localhost'); // Geralmente é localhost
define('DB_NAME', 'nome_do_seu_banco');
define('DB_USER', 'usuario_do_banco');
define('DB_PASS', 'senha_do_banco');

// URL do Sistema (IMPORTANTE: sem barra no final)
define('APP_URL', 'https://inscricoes-camara.cristinapolis.se.leg.br');

// Configurações de Email
// O sistema usa a função mail() do PHP. Certifique-se de que a hospedagem permite envio.
define('MAIL_FROM_ADDRESS', 'no-reply@cristinapolis.se.leg.br');
define('MAIL_FROM_NAME', 'SGC - Câmara Municipal');
define('MAIL_ADMIN_ADDRESS', 'admin@cristinapolis.se.leg.br');
```

## 5. Permissões
Certifique-se de que as seguintes pastas tenham permissão de escrita (755 ou 777):
- `storage/`
- `storage/certificates/`
- `storage/covers/`
- `storage/photos/`
- `storage/organization/`

## 6. Acesso Inicial
Após a instalação, acesse o sistema pelo navegador.

**Login de Super Admin:**
- **Usuário:** `root`
- **Senha:** `123456`

> **Importante:** Após o primeiro login, altere sua senha imediatamente e configure os dados da instituição no painel.
