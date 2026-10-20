-- ============================================================
-- SISRESERVA IFRO — Dados Iniciais de Demonstração (seed.sql)
-- Credenciais de teste documentadas no README.md
-- ============================================================

-- Se o nome do seu banco for diferente (ex: em provedores com prefixo epiz_123456_sisreserva),
-- comente ou remova a linha USE abaixo e selecione seu banco antes de importar:
USE `sisreserva`;

-- ------------------------------------------------------------
-- 1. CARGA INICIAL DE USUÁRIOS
-- Senhas criptografadas com password_hash(..., PASSWORD_DEFAULT):
-- admin@ifro.edu.br        -> Admin@123
-- professor@ifro.edu.br    -> Professor@123
-- maria.aluna@ifro.edu.br  -> Aluno@123
-- ------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `matricula`, `password_hash`, `role`) VALUES
(1, 'Administrador IFRO', 'admin@ifro.edu.br', '2026ADMIN01', '$2y$10$Z8Z8NoDPNIKZqeBAaSn4w.VREOv07uuswa6xpGRhRqoFjbD7T41si', 'ADMIN'),
(2, 'Professor João da Silva', 'professor@ifro.edu.br', '2026PROF01', '$2y$10$3gw7nKvbi/CQSb1HGOJFJ.ywzwK9sgU6rAyw6Nj5vN5.gavvP55om', 'SOLICITANTE'),
(3, 'Aluna Maria Oliveira', 'maria.aluna@ifro.edu.br', '2026ALUNO01', '$2y$10$uTxMvNTnvbQp.P4papYUTOdEtxQWwVlPGOZyzVYJRmuJN2uubhiHq', 'SOLICITANTE')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `password_hash` = VALUES(`password_hash`),
  `role` = VALUES(`role`);

-- ------------------------------------------------------------
-- 2. CARGA INICIAL DE RECURSOS
-- ------------------------------------------------------------
INSERT INTO `resources` (`id`, `name`, `category`, `capacity`, `location`, `description`, `status`) VALUES
(1, 'Labin 1', 'Laboratório', 35, 'Bloco A - Sala 101', '35 Computadores Core i5, 16GB RAM, projetor teto e ar-condicionado', 'ATIVO'),
(2, 'Labin 2', 'Laboratório', 35, 'Bloco A - Sala 102', '35 Computadores Core i7, 16GB RAM, quadro branco e switches de teste', 'ATIVO'),
(3, 'Labin 3', 'Laboratório', 30, 'Bloco A - Sala 103', '30 Computadores para aulas de desenvolvimento web e programação', 'ATIVO'),
(4, 'Labin 4', 'Laboratório', 30, 'Bloco A - Sala 104', 'Em manutenção técnica de ar-condicionado e cabeamento de rede', 'MANUTENCAO'),
(5, 'Redes', 'Laboratório', 25, 'Bloco B - Sala 201', 'Racks de roteadores Cisco, patch panels e bancadas para cabeamento', 'ATIVO'),
(6, 'Hardware', 'Laboratório', 25, 'Bloco B - Sala 202', 'Bancadas com mantas antiestáticas, kits de montagem e multímetros', 'ATIVO'),
(7, 'Manutenção', 'Laboratório', 20, 'Bloco B - Sala 203', 'Laboratório inativo temporariamente para reforma estrutural', 'INATIVO'),
(8, 'Projetor Epson HD 01', 'Equipamento', NULL, 'COGETI - Sala de Apoio', 'Projetor multimídia 3500 lumens com entrada HDMI e controle remoto', 'ATIVO'),
(9, 'Caixa de Som Amplificada', 'Equipamento', NULL, 'COGETI - Sala de Apoio', 'Caixa portátil Bluetooth com 2 microfones sem fio para palestras', 'ATIVO'),
(10, 'Kit de Robótica LEGO 01', 'Equipamento', NULL, 'Labin Redes - Armário 2', 'Kit LEGO Mindstorms com sensores e motores para projetos didáticos', 'ATIVO'),
(11, 'Adaptador HDMI para VGA 01', 'Equipamento', NULL, 'DAE - Balcão', 'Adaptador portátil com áudio P2 integrado', 'ATIVO')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `category` = VALUES(`category`),
  `capacity` = VALUES(`capacity`),
  `location` = VALUES(`location`),
  `description` = VALUES(`description`),
  `status` = VALUES(`status`);

-- ------------------------------------------------------------
-- 3. CARGA INICIAL DE RESERVAS DE DEMONSTRAÇÃO
-- ------------------------------------------------------------
INSERT INTO `reservations` (`id`, `user_id`, `resource_id`, `start_time`, `end_time`, `purpose`, `status`, `rejection_reason`, `reviewed_by_id`) VALUES
(1, 2, 1, DATE_ADD(CURDATE(), INTERVAL '1 14:00:00' DAY_SECOND), DATE_ADD(CURDATE(), INTERVAL '1 16:00:00' DAY_SECOND), 'Aula prática de Programação Web II - Turma 3B Informática', 'APROVADA', NULL, 1),
(2, 2, 8, DATE_ADD(CURDATE(), INTERVAL '2 08:00:00' DAY_SECOND), DATE_ADD(CURDATE(), INTERVAL '2 10:00:00' DAY_SECOND), 'Apresentação de seminários interdisciplinares', 'PENDENTE', NULL, NULL),
(3, 3, 2, DATE_ADD(CURDATE(), INTERVAL '3 10:00:00' DAY_SECOND), DATE_ADD(CURDATE(), INTERVAL '3 12:00:00' DAY_SECOND), 'Reunião de estudo em grupo de Redes de Computadores', 'RECUSADA', 'O laboratório estará reservado para manutenção preventiva da rede elétrica.', 1)
ON DUPLICATE KEY UPDATE
  `status` = VALUES(`status`),
  `purpose` = VALUES(`purpose`);
