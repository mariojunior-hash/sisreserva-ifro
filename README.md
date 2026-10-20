# SisReserva IFRO — Sistema Integrado de Agendamento e Reserva de Recursos

Sistema Web para gerenciamento, controle e agendamento de laboratórios de informática e equipamentos didáticos do **Instituto Federal de Educação, Ciência e Tecnologia de Rondônia (IFRO) — Campus Porto Velho Calama**.

Desenvolvido com foco em boas práticas, **PHP 8.x tradicional**, **MySQL/MariaDB (InnoDB, utf8mb4)**, **PDO com Prepared Statements**, **HTML5**, **CSS3** e **JavaScript Vanilla**, sem dependência de frameworks pesados, Node.js ou Composer para funcionamento essencial. O sistema funciona localmente no **XAMPP** (inclusive sem conexão com a internet) e é portável para hospedagens web compartilhadas gratuitas ou comerciais (como InfinityFree, AwardSpace, cPanel e similares).

---

## 1. Descrição e Finalidade do Sistema

O **SisReserva IFRO** foi concebido para solucionar os conflitos operacionais frequentes em ambientes educacionais, tais como:
1. **Conflito de Horários (Duplicidade):** Dois docentes ou monitores tentando utilizar o mesmo laboratório ou projetor no mesmo horário.
2. **Falta de Rastreabilidade:** Agendamentos registrados em planilhas dispersas ou livros físicos suscetíveis a perdas e rasuras.
3. **Comunicação Falha:** Ausência de justificativa documentada em casos de cancelamento ou recusa de reservas.

### Principais Recursos:
- **Controle de Acesso Baseado em Papéis (RBAC):** Perfis `SOLICITANTE` (professores, técnicos e alunos) e `ADMIN` (coordenação e suporte de laboratórios).
- **Validação Algorítmica de Conflitos no Servidor:** Fórmula matemática de sobreposição de intervalos temporal impedindo choques de horários.
- **Moderação Completa de Agendamentos:** Aprovação com revalidação de regras e rejeição obrigatória com justificativa mínima de 10 caracteres.
- **Gestão do Catálogo de Recursos:** Cadastro, edição, inativação e bloqueio de agendamento para recursos marcados como `MANUTENCAO` ou `INATIVO`.
- **Segurança Nativa:** Senhas criptografadas com `password_hash()`, proteção contra SQL Injection com PDO, proteção CSRF com tokens em sessão, escaping contra XSS (`htmlspecialchars`), cookies de sessão `HttpOnly` com `SameSite=Lax`.

---

## 2. Tecnologias Utilizadas e Versões Verificadas

| Componente | Tecnologia Adotada | Versão Verificada no Ambiente |
| :--- | :--- | :--- |
| **Linguagem Backend** | PHP (Paradigma Procedural/Estruturado Modular) | PHP 8.2.12 (compatível com PHP 8.0+) |
| **Banco de Dados** | MySQL / MariaDB (Engine InnoDB, Charset utf8mb4) | MariaDB 10.4+ / MySQL 8.0.x |
| **Camada de Acesso a Dados** | PHP Data Objects (PDO) com Prepared Statements | Driver `pdo_mysql` nativo |
| **Servidor Web Local** | Apache HTTP Server | Apache 2.4.58 (Win64) |
| **Frontend** | HTML5 semântico, CSS3 responsivo (estilo IFRO) e JS Vanilla | ECMAScript 6+ nativo (sem dependências) |
| **Pacote de Servidor Local** | XAMPP para Windows | XAMPP 8.2.12 |

> **Nota de Independência:** O projeto **NÃO** depende de Node.js, NPM, Composer, React, Next.js, Prisma, PostgreSQL ou serviços externos em nuvem para rodar.

---

## 3. Estrutura Real de Diretórios do Projeto

```text
sisreserva/
│
├── assets/                          # Arquivos estáticos servidos ao navegador
│   ├── css/
│   │   └── style.css                # Folha de estilo institucional IFRO (100% offline)
│   └── js/
│       └── app.js                   # Scripts Vanilla JS para menu responsivo e modais
│
├── config/                          # Configurações globais e banco de dados
│   ├── .htaccess                    # Bloqueio de acesso web direto (Require all denied)
│   ├── config.php                   # Sessão segura, BASE_URL, fuso horário e constantes RN
│   ├── database.php                 # Conexão singleton PDO com tratamento seguro de erros
│   ├── database.example.php         # Modelo de configuração sem senhas para versionamento
│   └── database.local.php           # Configurações locais (criado pelo desenvolvedor, no .gitignore)
│
├── database/                        # Scripts SQL versionados para o banco de dados
│   ├── .htaccess                    # Bloqueio de acesso web direto aos arquivos SQL
│   ├── schema.sql                   # DDL com tabelas, chaves primárias/estrangeiras e índices
│   └── seed.sql                     # Carga inicial com dados e usuários de demonstração
│
├── includes/                        # Componentes reutilizáveis e lógica compartilhada
│   ├── .htaccess                    # Bloqueio de acesso web direto
│   ├── auth.php                     # Módulo de autenticação, sessão e proteção RBAC
│   ├── flash.php                    # Sistema de mensagens flash em sessão (sucesso, erro)
│   ├── footer.php                   # Rodapé padrão institucional e inclusão do JS
│   ├── functions.php                # Sanitização XSS (e()), tokens CSRF e formatação de datas
│   └── header.php                   # Cabeçalho HTML5, navbar dinâmica por perfil e alertas
│
├── public/                          # Raiz pública da aplicação web (acessível pelo navegador)
│   ├── admin/                       # Área administrativa restrita ao perfil ADMIN
│   │   ├── aprovar.php              # Ação de aprovação de reserva com revalidação
│   │   ├── index.php                # Dashboard do Administrador com métricas e fila
│   │   ├── recurso_editar.php       # Formulário de edição de recurso
│   │   ├── recurso_excluir.php      # Exclusão de recurso do catálogo
│   │   ├── recurso_novo.php         # Formulário de cadastro de novo recurso
│   │   ├── recurso_salvar.php       # Processamento de inserção/atualização de recursos
│   │   ├── recursos.php             # Listagem administrativa de recursos
│   │   ├── rejeitar.php             # Rejeição com justificativa obrigatória (mín. 10 chars)
│   │   └── reservas.php             # Moderação geral de reservas com filtros
│   │
│   ├── reservas/                    # Área de agendamentos para usuários autenticados
│   │   ├── cancelar.php             # Cancelamento de reservas futuras
│   │   ├── criar.php                # Criação com transação atômica e checagem de conflitos
│   │   ├── minhas.php               # Histórico e situação das reservas do usuário
│   │   └── nova.php                 # Formulário de solicitação de agendamento
│   │
│   ├── cadastro.php                 # Tela de cadastro público de novos solicitantes
│   ├── dashboard.php                # Painel principal do Solicitante (resumo e atalhos)
│   ├── index.php                    # Página inicial institucional e catálogo em tempo real
│   ├── login.php                    # Tela de autenticação com validação de credenciais
│   └── logout.php                   # Encerramento seguro de sessão e limpeza de cookies
│
├── .env.example                     # Modelo de variáveis de ambiente para documentação
├── .gitignore                       # Regras de exclusão do Git (ignora credenciais e logs)
├── .htaccess                        # Configurações de segurança da raiz do Apache
├── DEPLOYMENT.md                    # Guia completo de implantação para outras pessoas/IAs
├── index.php                        # Redirecionador automático para public/index.php
└── README.md                        # Este documento
```

---

## 4. Instalação e Execução Local com XAMPP

### Passo 1: Instalação do XAMPP
1. Baixe o instalador oficial do XAMPP para Windows (com PHP 8.x): [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Instale mantendo marcados os módulos **Apache**, **MySQL** e **phpMyAdmin**.
3. O diretório padrão de instalação é `C:\xampp`.

### Passo 2: Localização da Pasta do Projeto
O projeto deve residir dentro de `htdocs`:
```text
C:\xampp\htdocs\sisreserva\
```

Se o seu repositório estiver em outro diretório do disco, crie uma junção de diretório no prompt de comando (`cmd.exe` como Administrador):
```cmd
mklink /J "C:\xampp\htdocs\sisreserva" "C:\Caminho\Onde\Voce\Clonou\sisreserva"
```

### Passo 3: Inicialização dos Serviços
1. Abra o **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`).
2. Clique em **Start** no módulo **Apache** (deve operar na porta 80 e 443).
3. Clique em **Start** no módulo **MySQL** (deve operar na porta 3306).

---

## 5. Criação e Importação do Banco de Dados

### Opção A: Pelo phpMyAdmin (Interface Gráfica)
1. Abra no navegador: `http://localhost/phpmyadmin/`
2. No menu lateral esquerdo, clique em **Novo** (New).
3. Digite o nome da base: `sisreserva`
4. Selecione a ordenação (Collation): `utf8mb4_unicode_ci`
5. Clique em **Criar**.
6. Selecione a base recém-criada `sisreserva`.
7. Clique na aba superior **Importar** (Import).
8. Em "Ficheiro a importar", selecione o arquivo:
   `C:\xampp\htdocs\sisreserva\database\schema.sql`
9. Clique no botão **Executar** no final da página.
10. *(Opcional para testes)* Repita o processo de importação para o arquivo `database/seed.sql` caso deseje carregar os usuários e laboratórios de demonstração.

### Opção B: Pela Linha de Comando (Terminal)
Abra o prompt de comando ou PowerShell e execute:
```bash
# Cria a base e importa a estrutura
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sisreserva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p sisreserva < "C:\xampp\htdocs\sisreserva\database\schema.sql"

# Importa a carga de dados de teste (opcional)
mysql -u root -p sisreserva < "C:\xampp\htdocs\sisreserva\database\seed.sql"
```
*(Caso seu MySQL não possua senha, apenas pressione Enter quando solicitado).*

---

## 6. Configuração da Conexão PHP / MySQL

O sistema adota um padrão de configuração em camadas para proteger dados confidenciais:

1. Na pasta `config/`, copie o arquivo de exemplo:
   - No Windows: Copie `config/database.example.php` e renomeie para `config/database.local.php`.
   - No terminal:
     ```bash
     copy config\database.example.php config\database.local.php
     ```
2. Abra `config/database.local.php` em um editor e ajuste as credenciais:
   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_PORT', '3306');
   define('DB_NAME', 'sisreserva');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Se o seu MySQL do XAMPP tiver senha, insira aqui (ex: 'sua_senha')
   define('DB_CHARSET', 'utf8mb4');
   ```
3. O arquivo `config/database.local.php` está incluído no `.gitignore` e **nunca será versionado**, mantendo suas senhas seguras.

---

## 7. Acesso à Aplicação e URLs Oficiais

Abra o navegador e acesse a aplicação:
👉 **[http://localhost/sisreserva/public/index.php](http://localhost/sisreserva/public/index.php)**  
*(Ou simplesmente [http://localhost/sisreserva/](http://localhost/sisreserva/), que redireciona automaticamente).*

### URLs Principais:
- **Página Inicial / Consulta Pública:** `http://localhost/sisreserva/public/index.php`
- **Login:** `http://localhost/sisreserva/public/login.php`
- **Cadastro:** `http://localhost/sisreserva/public/cadastro.php`
- **Dashboard do Solicitante:** `http://localhost/sisreserva/public/dashboard.php`
- **Solicitação de Reserva:** `http://localhost/sisreserva/public/reservas/nova.php`
- **Histórico de Reservas:** `http://localhost/sisreserva/public/reservas/minhas.php`
- **Painel Administrativo:** `http://localhost/sisreserva/public/admin/index.php`
- **Gerenciamento de Recursos:** `http://localhost/sisreserva/public/admin/recursos.php`
- **Moderação de Reservas:** `http://localhost/sisreserva/public/admin/reservas.php`

### Como Verificar se o Banco de Dados Conectou:
Ao acessar `http://localhost/sisreserva/public/index.php`, observe a barra de status logo abaixo do título:
- 🟢 **Badge Verde ("BANCO DE DADOS CONECTADO"):** Confirma que o PDO estabeleceu comunicação com o MySQL com sucesso e as consultas estão operacionais. O catálogo exibirá os recursos cadastrados.
- 🔴 **Alerta Vermelho ("ERRO DE CONEXÃO COM O BANCO DE DADOS"):** Indica falha no serviço MySQL ou credenciais divergentes. Siga o item 10 deste manual para diagnosticar.

---

## 8. Contas de Demonstração (Seed) e Procedimentos de Teste

### Procedimento Passo a Passo para Criar uma Conta e Testar o Login:
1. Acesse `http://localhost/sisreserva/public/cadastro.php` (ou clique no botão **Cadastrar-se** na barra de navegação).
2. Preencha os dados do formulário:
   - **Nome Completo:** ex: `Prof. Carlos Mendes`
   - **E-mail Institucional:** ex: `carlos.mendes@ifro.edu.br` (o sistema valida formato de e-mail)
   - **Matrícula / SIAPE:** ex: `2026PROF99`
   - **Senha e Confirmação:** mínimo de 6 caracteres (ex: `Senha@2026`)
3. Clique em **Concluir Cadastro**.
4. O sistema salva o registro de forma segura utilizando `password_hash(..., PASSWORD_DEFAULT)` e redireciona para a tela de login com alerta de sucesso.
5. Em `http://localhost/sisreserva/public/login.php`, digite o e-mail e a senha cadastrados e clique em **Entrar**.
6. A sessão do usuário (`SISRESERVA_SESSION`) é iniciada com perfil `SOLICITANTE` e o painel `public/dashboard.php` é carregado com as métricas do usuário.
7. Para encerrar, clique no botão **Sair** no menu superior (`public/logout.php`), que destrói a sessão e limpa o cookie com segurança.

---

### Contas de Demonstração Pré-carregadas (Seed):
Caso tenha importado o script `database/seed.sql`, você também pode utilizar as contas didáticas de demonstração:

| Perfil | E-mail de Acesso | Senha Padrão | Funcionalidades Habilitadas |
| :--- | :--- | :--- | :--- |
| **Administrador (ADMIN)** | `admin@ifro.edu.br` | `Admin@123` | Gestão de recursos, aprovação/rejeição de reservas, indicadores consolidados. |
| **Professor (SOLICITANTE)** | `professor@ifro.edu.br` | `Professor@123` | Consulta de grade, submissão de reservas, cancelamento de reservas próprias futuras. |
| **Aluna (SOLICITANTE)** | `maria.aluna@ifro.edu.br` | `Aluno@123` | Consulta e solicitações de laboratórios. |

> **Nota de Segurança:** As contas e senhas acima são destinadas estritamente a ambientes locais de avaliação acadêmica. Para ambientes de produção, crie um novo administrador ou gere uma nova senha utilizando:  
> `php -r "echo password_hash('SuaNovaSenhaForte!', PASSWORD_DEFAULT);"` e atualize a tabela `users`.

### Roteiro de Verificação das Funcionalidades:

1. **Teste de Cadastro:**
   - Acesse `public/cadastro.php`, preencha nome, e-mail institucional, matrícula e senha.
   - O sistema valida no servidor, gera o hash com `password_hash()` e redireciona para o login com mensagem de sucesso.
   - Tente cadastrar o mesmo e-mail novamente: o sistema bloqueia e exibe mensagem de duplicidade.
2. **Teste de Autenticação e RBAC:**
   - Faça login com o professor. Acesse o dashboard.
   - Tente digitar manualmente a URL `public/admin/index.php`: o servidor intercepta a requisição, nega o acesso e redireciona para o dashboard com alerta.
3. **Teste de Conflito de Horário:**
   - Como solicitante, acesse `public/reservas/nova.php` e agende o "Labin 1" para uma data futura das 14:00 às 16:00.
   - Tente em seguida solicitar o mesmo "Labin 1" para a mesma data das 15:00 às 17:00: o sistema barrará a solicitação no servidor com mensagem de conflito de horário.
4. **Teste de Recurso em Manutenção:**
   - Tente solicitar o "Labin 4" (configurado com status `MANUTENCAO`): o sistema impede o agendamento.
5. **Teste de Moderação:**
   - Faça logout e entre como administrador (`admin@ifro.edu.br`).
   - Acesse `public/admin/reservas.php`.
   - Na reserva pendente, teste a **Rejeição** digitando um motivo com menos de 10 caracteres (ex: "Não"): o sistema bloqueia.
   - Digite uma justificativa explicativa (ex: "Laboratório reservado para manutenção preventiva da rede") e confirme: o status vira `RECUSADA`.
   - Teste a **Aprovação** de uma reserva válida: o status vira `APROVADA`.

---

## 9. Matriz de Situação Real de Implementação

Todos os requisitos e regras de negócio especificados foram implementados no código e validados por testes automatizados e manuais:

| Requisito / Regra | Descrição | Status Real | Onde Está Implementado |
| :--- | :--- | :---: | :--- |
| **RF01 / RF02** | Cadastro e Login com Sessão Segura e Hash Bcrypt | **Concluído (100%)** | `public/cadastro.php`, `public/login.php`, `includes/auth.php` |
| **RF03 / RBAC** | Controle de Acesso por Perfil (`ADMIN` vs `SOLICITANTE`) | **Concluído (100%)** | `includes/auth.php` (`requireLogin()`, `requireAdmin()`) |
| **RF04 / RF05** | Catálogo e Consulta de Recursos em Tempo Real | **Concluído (100%)** | `public/index.php`, `public/admin/recursos.php` |
| **RF06** | Formulário de Criação de Reserva | **Concluído (100%)** | `public/reservas/nova.php` |
| **RN01 / RN02** | Antecedência mínima (2h) e máxima (30 dias) | **Concluído (100%)** | `public/reservas/criar.php` |
| **RN03 / RN04** | Duração mínima (45 min) e máxima contínua (4h) | **Concluído (100%)** | `public/reservas/criar.php` |
| **RN05 / RN06** | Bloqueio de agendamento em recursos `MANUTENCAO` ou `INATIVO` | **Concluído (100%)** | `public/reservas/criar.php`, `public/admin/aprovar.php` |
| **RN07 / RN08** | Proibição de reservas e aprovações no passado | **Concluído (100%)** | `public/reservas/criar.php`, `public/admin/aprovar.php` |
| **RN09** | Proibição de cancelamento de reservas passadas | **Concluído (100%)** | `public/reservas/cancelar.php` |
| **RN10** | Nova solicitação inicia com status `PENDENTE` | **Concluído (100%)** | `public/reservas/criar.php` |
| **Conflito de Horário** | Fórmula $\max(start_1, start_2) < \min(end_1, end_2)$ | **Concluído (100%)** | `public/reservas/criar.php`, `public/admin/aprovar.php` |
| **Concorrência** | Transações atômicas InnoDB (`FOR UPDATE`) | **Concluído (100%)** | `public/reservas/criar.php` |
| **Aprovação / Rejeição** | Moderação com justificativa obrigatória ($\ge 10$ chars) | **Concluído (100%)** | `public/admin/aprovar.php`, `public/admin/rejeitar.php` |
| **Cancelamento** | Solicitante cancela reserva própria futura | **Concluído (100%)** | `public/reservas/cancelar.php` |
| **Segurança** | PDO Prepared Statements, CSRF tokens, XSS escaping | **Concluído (100%)** | `config/database.php`, `includes/functions.php` |
| **Proteção de Pastas** | Bloqueio de `config/`, `database/`, `includes/` via Apache | **Concluído (100%)** | Arquivos `.htaccess` nas pastas protegidas |

---

## 10. Problemas Comuns e Soluções (Troubleshooting)

### 1. Erro `404 Not Found` ao clicar nos links ou acessar a página inicial
- **Causa:** O Apache não está localizando o subdiretório `/sisreserva/public/` ou o projeto foi clonado com outro nome de pasta (ex: `sisreserva-main` ou `projeto`).
- **Solução:**
  1. Certifique-se de que a pasta dentro de `C:\xampp\htdocs\` se chame exatamente `sisreserva` (ou seja, `C:\xampp\htdocs\sisreserva\`).
  2. Acesse diretamente pela URL completa: `http://localhost/sisreserva/public/index.php`.
  3. Verifique em `config/config.php` se a constante `BASE_URL` está definida como `define('BASE_URL', '/sisreserva/public');`.

### 2. Conexão Recusada (`Connection refused` ou MySQL parado)
- **Causa:** O serviço do MySQL não foi iniciado no XAMPP ou foi interrompido por erro de configuração.
- **Solução:**
  1. Abra o **XAMPP Control Panel** e clique em **Start** no módulo **MySQL**.
  2. Verifique se o texto do módulo fica com fundo verde e exibe a porta `3306`.
  3. Se o MySQL falhar ao iniciar, verifique se outro serviço como MariaDB ou MySQL standalone está rodando como serviço do Windows.

### 3. Banco de Dados Inexistente (`Unknown database 'sisreserva'`)
- **Causa:** O Apache e o MySQL estão rodando, mas a base de dados ainda não foi criada no MySQL.
- **Solução:**
  1. Acesse o phpMyAdmin em `http://localhost/phpmyadmin/`.
  2. Clique em **Novo**, digite o nome `sisreserva`, selecione a collation `utf8mb4_unicode_ci` e clique em **Criar**.
  3. Em seguida, acesse a aba **Importar** e execute o arquivo `database/schema.sql` (e opcionalmente `database/seed.sql`).

### 4. Erro de Acesso Negado (`Access denied for user 'root'@'localhost'`)
- **Causa:** O MySQL local possui senha configurada, mas o arquivo `config/database.local.php` está com senha vazia ou ausente.
- **Solução:**
  1. No Windows, copie `config/database.example.php` para `config/database.local.php`.
  2. Abra o arquivo em um editor e preencha a constante `DB_PASS` com a senha do seu usuário do banco:
     ```php
     define('DB_PASS', 'sua_senha_aqui');
     ```

### 5. Porta 80 do Apache Ocupada (Apache não inicia no XAMPP)
- **Causa:** Outro aplicativo (ex: IIS - Internet Information Services, Skype ou World Wide Web Publishing Service) está escutando na porta 80.
- **Solução:**
  - Opção A (Desativar IIS): Abra o PowerShell como Administrador e execute `iisreset /stop` ou desative o serviço `W3SVC`.
  - Opção B (Alterar porta do Apache): No XAMPP Control Panel, clique em **Config** no Apache -> `httpd.conf`, altere a linha `Listen 80` para `Listen 8080` e acesse via `http://localhost:8080/sisreserva/public/index.php`.

### 6. Conflito de Portas no MySQL (Porta 3306 ocupada)
- **Causa:** Outro serviço de banco de dados (ex: MySQL 8.0 instalado separadamente no Windows) já está escutando na porta 3306.
- **Solução:**
  1. Se você já tem o MySQL Server rodando no Windows na porta 3306, não é necessário rodar o MySQL do XAMPP; basta criar o banco `sisreserva` nele e configurar a senha no `config/database.local.php`.
  2. Caso queira que o MySQL do XAMPP rode em outra porta (ex: 3307), altere no XAMPP Control Panel e defina `define('DB_PORT', '3307');` no `config/database.local.php`.

---

## 11. Limitações Conhecidas da Versão 1.0

1. **Envio de Notificações por E-mail:** A versão 1.0 armazena todo o histórico e justificativas diretamente no banco de dados e na interface do usuário. Não possui envio de e-mails transacionais (SMTP) ativo, pois servidores locais XAMPP não possuem servidor de correio configurado por padrão.
2. **Integração Física com Portas/Armários:** O sistema gerencia o ciclo lógico e documental das reservas; a entrega das chaves físicas ou liberação das portas dos laboratórios permanece sob responsabilidade do técnico ou docente responsável pelo Labin.
3. **Dependência de Servidor Compatível com Apache `.htaccess`:** As regras de segurança contra download de arquivos `.sql` e de configuração dependem do módulo `mod_authz_core` do Apache ativo. Em servidores Nginx, essas regras devem ser traduzidas para a configuração do bloco `server`.
