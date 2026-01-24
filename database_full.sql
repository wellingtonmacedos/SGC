-- Configurações iniciais
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Tabelas Independentes (Parents)
-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `cpf` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','candidate') NOT NULL DEFAULT 'candidate',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `force_password_change` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`),
  UNIQUE KEY `uniq_users_cpf` (`cpf`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `cpf`, `phone`, `address`, `photo`, `email`, `username`, `password_hash`, `role`, `status`, `created_at`, `force_password_change`) VALUES
(1, 'Administrador', '00000000000', NULL, NULL, NULL, 'admin@localhost', NULL, '$2y$10$AMX6lY475tG1L0SzI.Tm.O2eIvGT3PZ7tVMTx43xYK4aiHiIMKDbS', 'admin', 'active', '2026-01-17 09:51:51', 0),
(2, 'Severino Nelson da Silva', '02153009553', '79999999999', 'Rua A', '981b7a49512346a3dca46fcd97fb15f7e6dccca3.jpg', 'severino@email.com', 'severino', '$2y$10$R9d7MiXcsPu27RPm9oK65OzlG/vNBDQTIqHt38XA6lHywgTEtxKse', 'candidate', 'active', '2026-01-17 10:53:47', 0),
(3, 'Super Usuário', '00000000001', NULL, NULL, NULL, 'root@sistema.leg', 'root', '$2y$10$4SvYTra71teAfdVoVvTrWuTHM5Ohf2LWDOo28UeC7GryELsB/0oZi', 'super_admin', 'active', '2026-01-22 10:00:53', 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `target_audience` text DEFAULT NULL,
  `workload` int(10) unsigned DEFAULT NULL,
  `instructor` varchar(150) NOT NULL,
  `period` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `allow_enrollment` tinyint(1) NOT NULL DEFAULT 1,
  `max_enrollments` int(10) unsigned DEFAULT 0,
  `created_at` datetime NOT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_courses_status` (`status`),
  KEY `idx_courses_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `courses`
--

INSERT INTO `courses` (`id`, `name`, `description`, `target_audience`, `workload`, `instructor`, `period`, `status`, `allow_enrollment`, `max_enrollments`, `created_at`, `date`, `time`, `location`, `cover_image`) VALUES
(1, 'Curso de Oratória', 'É um treinamento prático que desenvolve a comunicação e a autoconfiança, ensinando a estruturar o pensamento, usar a voz (tom, ritmo, volume) e a linguagem corporal (postura, gestos) para falar bem em público, em vídeos ou reuniões, focando em técnicas como storytelling e superação do medo, com muita prática e feedback, muitas vezes com gravações para análise do progresso.', NULL, 8, 'Ana Maria', '', 'active', 1, 0, '2026-01-17 10:52:27', '2026-01-29', '18:00:00', 'Camara Municipal', '39ebfae852524b15a551f21da03e1ea1910ffa73_1769042250.png');

-- --------------------------------------------------------

--
-- Estrutura da tabela `organization_settings`
--

DROP TABLE IF EXISTS `organization_settings`;
CREATE TABLE `organization_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_name` varchar(255) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `institutional_text` text DEFAULT NULL,
  `login_title` varchar(255) DEFAULT 'Bem-vindo ao SGC',
  `login_subtitle` varchar(255) DEFAULT 'Faça login para continuar',
  `login_primary_color` varchar(7) DEFAULT '#0d1b2a',
  `login_background_color` varchar(7) DEFAULT '#0d1b2a',
  `login_background_image` varchar(255) DEFAULT NULL,
  `login_icon` varchar(50) DEFAULT 'fas fa-graduation-cap',
  `login_logo` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `organization_settings`
--

INSERT INTO `organization_settings` (`id`, `organization_name`, `cnpj`, `email`, `phone`, `address`, `city`, `state`, `zip_code`, `logo`, `institutional_text`, `updated_at`) VALUES
(1, 'Câmara Municipal de Cristinápolis', '32.766.388/0001-22', 'camara@camaradecristinapolis.se.gov.br', '79 3542-1314', 'Praça da Bandeira, 149  – Centro', 'Cristinápolis', 'SE', '49270-000', 'logo_1768998716.png', '', '2026-01-21 09:31:56');

-- --------------------------------------------------------
-- 2. Tabelas Dependentes (Children)
-- --------------------------------------------------------

--
-- Estrutura da tabela `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `status` enum('enrolled','completed','certificate_available') NOT NULL DEFAULT 'enrolled',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_enrollments_user` (`user_id`),
  KEY `idx_enrollments_course_id` (`course_id`),
  KEY `idx_enrollments_created_at` (`created_at`),
  CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'certificate_available', '2026-01-17 10:54:44', '2026-01-17 11:22:05');

-- --------------------------------------------------------

--
-- Estrutura da tabela `certificates`
--

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_certificates_enrollment` (`enrollment_id`),
  CONSTRAINT `fk_certificates_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `certificates`
--

INSERT INTO `certificates` (`id`, `enrollment_id`, `file_name`, `original_name`, `file_size`, `mime_type`, `created_at`) VALUES
(1, 1, 'aff71101e1205e6b769aac2a4713382528fd525d_1768659725.pdf', 'Relatório de Sistema GED - CMI - SETEMBRO 2025.pdf', 353203, 'application/pdf', '2026-01-17 11:22:05');

-- --------------------------------------------------------

--
-- Estrutura da tabela `backups`
--

DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('db','files','full') NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_backups_user` (`created_by`),
  CONSTRAINT `fk_backups_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_password_resets_token` (`token`),
  KEY `fk_password_resets_user` (`user_id`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_logs_user` (`user_id`),
  KEY `idx_logs_action` (`action`),
  KEY `idx_logs_created_at` (`created_at`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Restaurar verificações
--
SET FOREIGN_KEY_CHECKS=1;
