# SisReserva IFRO — Guia de Implantação e Publicação (DEPLOYMENT.md)

Este documento foi elaborado para orientar qualquer pessoa desenvolvedora, administradora de sistemas ou agente de inteligência artificial que receba este repositório do **SisReserva IFRO** a inspecionar, configurar, testar e implantar o sistema com sucesso em ambiente local ou em provedores de hospedagem na web.

---

> [!IMPORTANT]
> **Esclarecimento Fundamental sobre o GitHub:**  
> O GitHub armazena apenas o código-fonte versionado do projeto. O serviço **GitHub Pages NÃO executa código backend em PHP nem possui banco de dados MySQL** (ele serve exclusivamente páginas HTML/CSS/JS estáticas). Para publicar o sistema na internet, é mandatório utilizar uma **hospedagem web compatível com PHP 8.x e MySQL/MariaDB**.

---

## 1. Como Inspecionar o Projeto

Ao clonar o repositório, inspecione a estrutura de diretórios para compreender a separação de responsabilidades da aplicação:

1. **`public/` (Raiz Pública da Aplicação):**
   - Todos os arquivos acessíveis diretamente pelo navegador residem aqui (`index.php`, `login.php`, `cadastro.php`, `dashboard.php`, `logout.php`).
   - Contém o subdiretório `public/reservas/` para fluxos do solicitante (`nova.php`, `criar.php`, `minhas.php`, `cancelar.php`).
   - Contém o subdiretório `public/admin/` para fluxos administrativos (`index.php`, `recursos.php`, `reservas.php`, `aprovar.php`, `rejeitar.php`, etc.).
2. **`config/` (Camada de Configuração):**
   - `config.php`: Gerencia sessões seguras (`HttpOnly`, `SameSite=Lax`), fuso horário oficial (`America/Porto_Velho`), regras de negócio e cálculo da `BASE_URL`.
   - `database.php`: Estabelece a conexão singleton via **PDO** com tratamento seguro de exceções (não vaza credenciais nem erros de SQLSTATE na tela).
   - `database.example.php`: Modelo de configuração limpo para ser copiado.
3. **`includes/` (Módulos Reutilizáveis):**
   - `auth.php`: Controle de acesso no servidor e verificação de permissões RBAC (`requireLogin()`, `requireAdmin()`).
   - `functions.php`: Funções de sanitização XSS (`e()`), geração e checagem de tokens CSRF (`csrfField()`, `verifyCsrfToken()`) e formatação de datas.
   - `flash.php`: Mecanismo de alertas temporários em sessão.
   - `header.php` e `footer.php`: Templates institucionais com navbar dinâmica por perfil.
4. **`database/` (Camada de Persistência):**
   - `schema.sql`: Script DDL reproduzível com todas as tabelas em **InnoDB**, chaves estrangeiras e índices compostos de conflito.
   - `seed.sql`: Carga opcional de dados e usuários de demonstração não sensíveis.
5. **`assets/` (Recursos Estáticos):**
   - `css/style.css` e `js/app.js`: Interface institucional responsiva que funciona **100% offline** (sem CDNs externas obrigatórias).
6. **Segurança de Diretórios (`.htaccess`):**
   - Os diretórios `config/`, `database/` e `includes/` possuem arquivos `.htaccess` contendo `Require all denied`, impedindo o download direto de scripts PHP internos e arquivos `.sql` por usuários da web.

---

## 2. Requisitos de Software e Versões

Para que o sistema opere sem inconsistências, o ambiente de hospedagem deve atender a:

- **Servidor Web:** Apache 2.4+ com módulos `mod_rewrite` e `mod_authz_core` ativos (para processar as regras de proteção dos arquivos `.htaccess`).
- **PHP:** Versão 8.0, 8.1, 8.2 ou superior.
- **Extensões Obrigatórias do PHP:**
  - `pdo` e `pdo_mysql` (para conexão segura com o banco).
  - `session` (para gestão de autenticação e mensagens flash).
  - `mbstring` (para manipulação e contagem de caracteres UTF-8 em validações).
- **Banco de Dados:** MySQL 5.7+ ou MariaDB 10.4+ com suporte a mecanismo **InnoDB** e conjunto de caracteres **utf8mb4**.

---

## 3. Como Executar Localmente no XAMPP

1. **Baixar e Instalar o XAMPP:**
   - Instale o XAMPP no Windows (diretório padrão: `C:\xampp`).
2. **Posicionar a Pasta do Projeto:**
   - Clone ou copie o repositório para: `C:\xampp\htdocs\sisreserva\`.
3. **Iniciar os Serviços:**
   - No painel do XAMPP, inicie **Apache** (portas 80/443) e **MySQL** (porta 3306).
4. **Configurar as Credenciais Locais:**
   - Copie `config/database.example.php` para `config/database.local.php`.
   - Ajuste a constante `DB_PASS` caso seu MySQL local possua senha. Se for a instalação padrão sem senha do XAMPP, mantenha `define('DB_PASS', '');`.

---

## 4. Como Criar e Configurar o Banco de Dados

### Via phpMyAdmin:
1. Acesse: `http://localhost/phpmyadmin/`.
2. Clique em **Novo** (New database), informe o nome `sisreserva` e o collation `utf8mb4_unicode_ci`.
3. Selecione a base `sisreserva` e clique na aba **Importar**.
4. Selecione `database/schema.sql` e execute.
5. *(Opcional)* Importe também `database/seed.sql` para carregar 11 recursos do IFRO Calama e contas de teste.

### Via Linha de Comando:
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sisreserva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p sisreserva < database/schema.sql
mysql -u root -p sisreserva < database/seed.sql
```

---

## 5. Como Testar a Aplicação Localmente

1. Abra o navegador e acesse:
   👉 **`http://localhost/sisreserva/public/index.php`**
2. Verifique se o selo verde **"BANCO DE DADOS CONECTADO"** é exibido no topo.
3. Teste o acesso às contas padrão (caso tenha importado `seed.sql`):
   - **Administrador:** `admin@ifro.edu.br` / senha: `Admin@123`
   - **Professor (Solicitante):** `professor@ifro.edu.br` / senha: `Professor@123`
4. Teste os fluxos principais:
   - Realizar logout e criar uma nova conta discente em `public/cadastro.php`.
   - Como solicitante, submeter uma reserva em `public/reservas/nova.php`.
   - Como administrador, acessar `public/admin/reservas.php` para aprovar ou rejeitar a reserva com justificativa.

---

## 6. Como Escolher uma Hospedagem Web (PHP + MySQL)

Para publicar o sistema na internet, selecione um provedor que ofereça suporte nativo a PHP e MySQL. Exemplos comuns:

- **Hospedagens Gratuitas para Testes/Apresentações Acadêmicas:**
  - **InfinityFree:** Oferece PHP 8.x, MySQL e cPanel (vPanel) gratuito.
  - **AwardSpace:** Suporta PHP, phpMyAdmin e 1 base de dados MySQL no plano gratuito.
- **Hospedagens Compartilhadas Comerciais:**
  - Hostinger, Locaweb, KingHost, HostGator ou qualquer provedor que disponibilize painel **cPanel** tradicional.
- **Servidores VPS / Cloud:**
  - DigitalOcean, AWS EC2, Linode ou Hetzner (onde você instala a stack LAMP: Linux, Apache, MySQL, PHP).

---

## 7. Como Criar o Banco Remoto e Importar a Estrutura

Em ambientes de hospedagem compartilhada, o painel do provedor impõe prefixos obrigatórios no nome do banco e do usuário.

1. Acesse o painel da sua hospedagem (ex: cPanel / vPanel).
2. Procure pela ferramenta **Bases de Dados MySQL** (MySQL Database Wizard).
3. Crie uma nova base (ex: `epiz_34567890_sisreserva`).
4. Crie um novo usuário para o banco (ex: `epiz_34567890_admin`) com uma senha forte.
5. Associe o usuário à base de dados com privilégio total (**ALL PRIVILEGES**).
6. Anote os dados gerados pelo painel:
   - **MySQL Host:** Frequentemente é um endereço específico do cluster (ex: `sql123.infinityfree.com` ou `localhost`).
   - **Database Name:** Nome completo gerado (com prefixo).
   - **Database User:** Usuário completo (com prefixo).
   - **Database Password:** A senha definida.
7. Abra o **phpMyAdmin** do provedor e selecione a base de dados criada.
8. Clique na aba **Importar**, envie o arquivo `database/schema.sql` e execute.
   *(Nota: O script `schema.sql` possui uma instrução condicional; se o phpMyAdmin remoto emitir aviso sobre `CREATE DATABASE`, certifique-se de que a base já esteja selecionada antes de executar).*

---

## 8. Como Configurar as Credenciais sem Expô-las no Git

> [!CAUTION]
> **NUNCA** suba arquivos contendo senhas reais ou strings de conexão com o banco para o repositório do GitHub.

1. No servidor remoto (via Gerenciador de Arquivos do painel ou FTP), navegue até a pasta `config/`.
2. Crie o arquivo `config/database.local.php` baseado em `config/database.example.php`.
3. Preencha as constantes com os dados fornecidos pelo seu provedor de hospedagem:
   ```php
   <?php
   declare(strict_types=1);

   define('DB_HOST', 'sql123.suahospedagem.com'); // Host fornecido pela hospedagem
   define('DB_PORT', '3306');
   define('DB_NAME', 'epiz_34567890_sisreserva');   // Nome do banco remoto com prefixo
   define('DB_USER', 'epiz_34567890_admin');       // Usuário do banco remoto
   define('DB_PASS', 'SuaSenhaSeguraAqui123!');    // Senha do usuário do banco
   define('DB_CHARSET', 'utf8mb4');
   ```
4. O arquivo `database.php` detectará automaticamente o arquivo local e carregará as credenciais em produção.

---

## 9. Como Transferir os Arquivos para a Hospedagem

### Método 1: Via Gerenciador de Arquivos do Painel (Recomendado pela Rapidez)
1. No seu computador, selecione todos os arquivos da pasta do projeto e compacte-os em formato `.zip`.
   *(Atenção: Não compacte a pasta `.git` nem `config/database.local.php`).*
2. No painel de controle da hospedagem, abra o **Gerenciador de Arquivos** (File Manager).
3. Navegue até o diretório raiz web (geralmente chamado de `htdocs` ou `public_html`).
4. Faça o upload do arquivo `.zip` e clique em **Extrair** (Extract).

### Método 2: Via Cliente FTP (FileZilla)
1. Abra o FileZilla e conecte-se utilizando as credenciais FTP fornecidas pela sua hospedagem (Host FTP, Usuário FTP, Senha e Porta 21).
2. No painel remoto (lado direito), entre na pasta `htdocs` ou `public_html`.
3. No painel local (lado esquerdo), selecione todos os arquivos do projeto e envie para o servidor.

---

## 10. Como Configurar o Diretório Público e as Rotas (`BASE_URL`)

A arquitetura do SisReserva IFRO foi projetada para adaptar-se dinamicamente ao endereço em que for implantada:

### Cenário A: Hospedagem onde o domínio aponta para a raiz do repositório
*(Ex: `http://meusite.infinityfreeapp.com/public/index.php`)*
- A raiz do projeto contém um arquivo `index.php` que realiza o redirecionamento automático para a pasta pública:
  ```php
  header('Location: ' . BASE_URL . '/index.php');
  ```
- O arquivo `config/config.php` possui a constante:
  ```php
  define('BASE_URL', '/sisreserva/public');
  ```
- Caso o projeto seja publicado na raiz do domínio (e não dentro de um subdiretório `/sisreserva/`), ajuste `config/config.php` para refletir o caminho correto:
  ```php
  define('BASE_URL', '/public');
  define('ASSETS_URL', '/assets');
  ```

### Cenário B: Hospedagem ou VPS onde o DocumentRoot pode ser configurado para `public/`
Se o painel do seu servidor permitir apontar a raiz do domínio diretamente para a pasta `public/`:
- Altere `config/config.php`:
  ```php
  define('BASE_URL', '');
  define('ASSETS_URL', '/assets');
  ```
- Desta forma, a URL fica limpa: `https://meusite.com/index.php`, `https://meusite.com/login.php`.

---

## 11. Como Configurar HTTPS (Criptografia no Tráfego)

1. **Ativação do Certificado SSL:**
   - Na maioria das hospedagens modernas (inclusive gratuitas como InfinityFree), existe a opção **Free SSL Certificates** no painel de controle.
   - Solicite a emissão do certificado Let's Encrypt gratuito com validação DNS automática.
2. **Comportamento do SisReserva com HTTPS:**
   - O arquivo `config/config.php` já possui detecção nativa de HTTPS:
     ```php
     $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                 (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
     ```
   - Ao rodar em HTTPS, o sistema ativa automaticamente o atributo `Secure` nos cookies de sessão, protegendo as credenciais contra interceptações de rede (mitm).
3. **Redirecionamento Forçado (Opcional):**
   - Caso queira forçar todo o tráfego HTTP para HTTPS, adicione ao `.htaccess` da raiz:
     ```apache
     RewriteEngine On
     RewriteCond %{HTTPS} off
     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
     ```

---

## 12. Checklist de Testes no Ambiente Publicado (Pós-Deploy)

Após transferir os arquivos e importar a base de dados no servidor remoto, execute esta rotina de homologação:

- [ ] **Acesso Inicial:** Abra a URL pública do site e confirme que a folha de estilos `style.css` carrega normalmente.
- [ ] **Selo de Conexão com o Banco:** Certifique-se de que a página inicial exibe o selo verde **"BANCO DE DADOS CONECTADO"**. Se exibir erro, revise `config/database.local.php`.
- [ ] **Teste de Proteção de Arquivos:** Tente acessar diretamente no navegador `https://seudominio.com/config/database.php` e `https://seudominio.com/database/schema.sql`. Ambos **DEVEM retornar HTTP 403 Forbidden**.
- [ ] **Novo Cadastro:** Realize o cadastro de um novo usuário comum e verifique se ele é gravado no banco de dados remoto.
- [ ] **Autenticação:** Faça login com o usuário criado e confirme o redirecionamento ao `dashboard.php`.
- [ ] **Regra de Conflito de Agendamento:** Realize duas solicitações de reserva para o mesmo recurso e horário para validar a prevenção de conflitos no servidor de produção.
- [ ] **Auditoria de Sessão:** Realize logout e tente pressionar o botão "Voltar" do navegador para garantir que as rotas autenticadas continuem bloqueadas.

---

## 13. Limitações e Ações Necessárias Antes da Produção Definitiva

Antes de disponibilizar o sistema para uso definitivo por toda a comunidade escolar do IFRO Calama:

1. **Troca Imediata das Senhas de Demonstração:**
   - Caso o arquivo `seed.sql` tenha sido importado, acesse o banco via phpMyAdmin ou use uma conta administrativa para alterar as senhas padrões de teste (`Admin@123`, `Professor@123`, `Aluno@123`).
2. **Definição de Rotina de Backup:**
   - No painel da hospedagem (cPanel), configure o agendamento de backups automáticos periódicos (diário ou semanal) da base MySQL para resguardar o histórico de agendamentos.
3. **Monitoramento de Espaço e Cotas em Hospedagens Gratuitas:**
   - Provedores gratuitos impõem limites diários de requisições e conexões simultâneas ao MySQL. Caso a aplicação atenda a centenas de acessos simultâneos em períodos de pico de matrículas ou eventos, recomenda-se migrar para um plano compartilhado básico com suporte técnico estável.
