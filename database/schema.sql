-- ============================================================
-- SISRESERVA IFRO — Sistema de Agendamento e Reserva de Recursos
-- Banco de Dados: MySQL / MariaDB (InnoDB, utf8mb4)
-- Componente Curricular: Desenvolvimento de Sistemas
-- IFRO Campus Porto Velho Calama
-- ============================================================

-- ------------------------------------------------------------
-- CRIAÇÃO DO BANCO (Para XAMPP / Servidores Locais):
-- Se estiver em hospedagem compartilhada (cPanel, InfinityFree, AwardSpace),
-- o banco já deve ser criado previamente pelo painel da hospedagem.
-- Nesse caso, selecione o banco no phpMyAdmin antes de importar
-- e remova/comente as 2 linhas abaixo se o provedor não permitir CREATE DATABASE:
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `sisreserva`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `sisreserva`;

-- ------------------------------------------------------------
-- 1. TABELA DE USUÁRIOS (users)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL COMMENT 'Nome completo do usuário',
  `email` VARCHAR(150) NOT NULL COMMENT 'E-mail institucional único (normalizado em minúsculas)',
  `matricula` VARCHAR(30) NOT NULL COMMENT 'Matrícula discente ou SIAPE',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Hash da senha gerado com password_hash()',
  `role` ENUM('SOLICITANTE', 'ADMIN') NOT NULL DEFAULT 'SOLICITANTE' COMMENT 'Perfil de acesso do usuário',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  UNIQUE KEY `uk_users_matricula` (`matricula`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. TABELA DE RECURSOS (resources)
-- Laboratórios de Informática e Equipamentos Didáticos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resources` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'Nome do recurso (ex: Labin 1, Projetor)',
  `category` VARCHAR(50) NOT NULL COMMENT 'Categoria (Laboratório ou Equipamento)',
  `capacity` INT NULL DEFAULT NULL COMMENT 'Capacidade de pessoas (para salas/laboratórios)',
  `location` VARCHAR(100) NOT NULL COMMENT 'Localização física (ex: Bloco A - Sala 101)',
  `description` TEXT NULL COMMENT 'Descrição detalhada dos equipamentos instalados',
  `status` ENUM('ATIVO', 'MANUTENCAO', 'INATIVO') NOT NULL DEFAULT 'ATIVO' COMMENT 'Estado operacional',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resources_status` (`status`),
  KEY `idx_resources_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. TABELA DE RESERVAS (reservations)
-- Solicitações, Agendamentos e Histórico
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT 'Usuário que solicitou a reserva',
  `resource_id` INT UNSIGNED NOT NULL COMMENT 'Recurso reservado',
  `start_time` DATETIME NOT NULL COMMENT 'Data e hora de início',
  `end_time` DATETIME NOT NULL COMMENT 'Data e hora de término',
  `purpose` TEXT NOT NULL COMMENT 'Finalidade/Justificativa da aula ou evento',
  `status` ENUM('PENDENTE', 'APROVADA', 'RECUSADA', 'CANCELADA') NOT NULL DEFAULT 'PENDENTE' COMMENT 'Status da reserva',
  `rejection_reason` TEXT NULL DEFAULT NULL COMMENT 'Justificativa obrigatória (mín. 10 chars) em caso de recusa',
  `reviewed_by_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Administrador que aprovou/recusou',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  -- Índices compostos para otimização de verificação de conflitos e listagens
  KEY `idx_conflict_check` (`resource_id`, `start_time`, `end_time`, `status`),
  KEY `idx_user_history` (`user_id`, `start_time`),
  KEY `idx_status_filter` (`status`, `start_time`),
  KEY `fk_reservations_reviewer` (`reviewed_by_id`),
  -- Restrições de Chaves Estrangeiras
  CONSTRAINT `fk_reservations_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservations_resource`
    FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservations_reviewer`
    FOREIGN KEY (`reviewed_by_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
