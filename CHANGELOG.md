# Changelog — SisReserva IFRO

Todas as mudanças graduais, decisões técnicas e revisões humanas relevantes deste projeto estão documentadas neste arquivo, seguindo o padrão [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [1.1.0] - 2026-10-20

### Adicionado
- **Carregador Nativo de Variáveis de Ambiente (`.env`):** Implementada rotina pura em PHP (sem pacotes de terceiros) no arquivo `config/database.php` para carregar variáveis de um arquivo `.env` na raiz do projeto, caso presente.
- **Roteiro Completo de Instalação e Testes no `README.md`:** Detalhamento passo a passo para execução em Windows com XAMPP, testes de cadastro, fluxo de login e validação visual de conexão com o banco.
- **Seção de Resolução de Problemas no `README.md`:** Guias de solução para porta 80 ocupada (IIS), porta 3306 ocupada, conexão recusada e banco inexistente.
- **Guia de Implantação para Produção (`DEPLOYMENT.md`):** Instruções para servidores Apache, cPanel e hospedagens compartilhadas gratuitas (InfinityFree, AwardSpace).
- **Especificação Técnica Final (`spec.md`):** Documento acadêmico completo com arquitetura real, modelos de dados, casos de uso, regras de negócio e guia minucioso de instalação.

### Modificado
- **Sanitização de Senhas em Documentação:** Removidas menções explícitas à senha do ambiente local de desenvolvimento nos arquivos públicos e de exemplo, substituindo por placeholders genéricos (`sua_senha`).
- **Reforço de Regras do `.gitignore`:** Inclusão de regras para capturar `*.local.php`, `config/*.local.php`, `.env`, dumps de bancos com dados reais e logs de erro.

### Corrigido
- **Navegação e Estrutura de URLs:** Correção das URLs relativas absolutas (`/login.php`, `/cadastro.php`) que geravam `404 Not Found` no Apache sob o subdiretório `/sisreserva/public/`. Padronização da constante `BASE_URL = '/sisreserva/public'` em `config/config.php`.
- **Isolamento de Credenciais Locais:** Criação de `config/database.example.php` (sem credenciais) e isolamento da senha do banco em `config/database.local.php` (ignorado no Git).

---

## [1.0.0] - 2026-09-22

### Decisão Arquitetural e Revisão Humana Fundamental
- **Substituição da Stack Hipotética:** A concepção preliminar da disciplina previa um projeto baseado em Next.js, React, Node.js, Prisma ORM e PostgreSQL na nuvem (Vercel).
- **Decisão Humana e Reorientação do Projeto:** O usuário instruiu a alteração mandatória da arquitetura para eliminar qualquer dependência de Node.js, React, Prisma e Composer.
- **Implementação Real Adotada:**
  - Backend: **PHP 8.x nativo** estruturado modular com **PDO** e **Prepared Statements**.
  - Banco de Dados: **MySQL / MariaDB** (Engine InnoDB, Charset utf8mb4).
  - Frontend: **HTML5 semântico**, **CSS3 responsivo** e **JavaScript Vanilla** (100% offline, sem CDNs externas).
  - Servidor Local: **Apache HTTP Server** via **XAMPP**.

### Funcionalidades Implementadas
- **Autenticação e Sessão:** Login, autocadastro de solicitantes, logout seguro com expiração de cookie (`SISRESERVA_SESSION`, `HttpOnly`, `SameSite=Lax`).
- **Controle de Acesso (RBAC):** Restrição de páginas e ações por perfil (`ADMIN` vs `SOLICITANTE`).
- **Catálogo de Recursos:** Visualização em tempo real de laboratórios de informática (Labins 1 a 4, Redes, Hardware, Manutenção) e equipamentos didáticos (projetores, kits robótica).
- **CRUD Administrativo de Recursos:** Cadastro, edição e inativação lógica de recursos pelo administrador.
- **Motor de Reservas com Prevenção de Conflitos:** Validação temporal algorítmica no servidor impedindo sobreposição de reservas aprovadas ou pendentes no mesmo recurso.
- **Moderação de Reservas:** Aprovação direta e rejeição administrativa com obrigatoriedade de justificativa explicativa ($\ge 10$ caracteres).
- **Gestão do Solicitante:** Painel "Minhas Reservas" com cancelamento de reservas futuras próprias.

---

## Decisões Técnicas e Itens Pendentes para Versões Futuras

1. **Envio de Notificações por E-mail (SMTP):**
   - *Situação na v1.x:* Desativado por padrão, pois servidores locais XAMPP não possuem serviço SMTP configurado nativamente. Todo o histórico de aprovações e recusas é exibido na interface do usuário.
   - *Decisão para v2.0:* Avaliar a inclusão opcional de biblioteca leve ou configuração SMTP via `mail()` quando hospedado em produção.
2. **Integração Física com Acesso aos Laboratórios:**
   - *Situação na v1.x:* O sistema gere o ciclo lógico e documental das reservas. A liberação das chaves físicas e o acompanhamento presencial continuam sob responsabilidade da coordenação e monitores de Labin.
