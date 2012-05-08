-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 08-05-2012 a las 14:29:46
-- Versión del servidor: 5.5.9
-- Versión de PHP: 5.2.17

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `anidcore`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_attachments`
--

CREATE TABLE IF NOT EXISTS `ac_attachments` (
  `attachment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL COMMENT 'image, video, file, etc',
  `filepath` varchar(255) DEFAULT NULL COMMENT 'relative path from AC_SITE_PATH',
  `filename` varchar(255) DEFAULT NULL COMMENT 'filename without path',
  `language_fk` bigint(20) unsigned DEFAULT NULL COMMENT 'file content''s lang',
  `category` varchar(20) DEFAULT NULL COMMENT 'i.e.: gallery name',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_visible` tinyint(1) unsigned DEFAULT '1',
  `sort_order` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`attachment_id`),
  KEY `filelanguage_fk` (`language_fk`),
  KEY `idx_full` (`type`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_attachments`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_attachmentsml`
--

CREATE TABLE IF NOT EXISTS `ac_attachmentsml` (
  `attachmentsml_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attachment_fk` bigint(20) unsigned NOT NULL,
  `language_fk` bigint(20) unsigned NOT NULL,
  `is_translated` tinyint(1) unsigned DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`attachmentsml_id`),
  UNIQUE KEY `u_attachment_language` (`attachment_fk`,`language_fk`),
  KEY `attachment_fk` (`attachment_fk`),
  KEY `language_fk` (`language_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_attachmentsml`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_comments`
--

CREATE TABLE IF NOT EXISTS `ac_comments` (
  `comment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `language_fk` bigint(20) unsigned DEFAULT NULL,
  `user_fk` bigint(20) unsigned DEFAULT NULL,
  `author_name` varchar(50) NOT NULL,
  `author_email` varchar(255) NOT NULL,
  `author_url` varchar(255) DEFAULT NULL,
  `author_ip` varchar(10) NOT NULL,
  `status` varchar(20) DEFAULT 'pending' COMMENT 'pending,approved,spam',
  PRIMARY KEY (`comment_id`),
  KEY `user_fk` (`user_fk`),
  KEY `language_fk` (`language_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_comments`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_documents`
--

CREATE TABLE IF NOT EXISTS `ac_documents` (
  `document_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `type` varchar(20) DEFAULT 'page',
  `status` varchar(20) DEFAULT 'draft' COMMENT 'published,draft,scheduled,deleted',
  `function` varchar(50) DEFAULT NULL,
  `template` varchar(50) DEFAULT NULL,
  `is_indexable` tinyint(1) unsigned DEFAULT '1',
  `is_page` tinyint(1) unsigned DEFAULT '1',
  `parent_fk` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `modified_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`document_id`),
  UNIQUE KEY `name` (`name`),
  KEY `parent_fk` (`parent_fk`),
  KEY `idx_full` (`type`,`status`),
  KEY `modified_by` (`modified_by`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Volcar la base de datos para la tabla `ac_documents`
--

INSERT INTO `ac_documents` (`document_id`, `name`, `type`, `status`, `function`, `template`, `is_indexable`, `is_page`, `parent_fk`, `created_by`, `modified_by`, `created_at`, `modified_at`, `sort_order`) VALUES
(1, 'index', 'page', 'published', NULL, NULL, 1, 1, NULL, 1, NULL, '2012-02-24 12:00:00', '2012-02-24 12:00:00', 1),
(2, 'error', 'page', 'published', NULL, NULL, 0, 1, NULL, 1, NULL, '2012-02-24 12:00:00', '2012-02-24 12:00:00', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_documentsml`
--

CREATE TABLE IF NOT EXISTS `ac_documentsml` (
  `documentsml_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_fk` bigint(20) unsigned NOT NULL,
  `language_fk` bigint(20) unsigned NOT NULL,
  `is_translated` tinyint(1) unsigned DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  `content` mediumtext,
  `window_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  PRIMARY KEY (`documentsml_id`),
  UNIQUE KEY `u_document_language` (`document_fk`,`language_fk`),
  KEY `document_fk` (`document_fk`),
  KEY `language_fk` (`language_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_documentsml`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_document_terms`
--

CREATE TABLE IF NOT EXISTS `ac_document_terms` (
  `document_term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_fk` bigint(20) unsigned NOT NULL,
  `term_fk` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`document_term_id`),
  UNIQUE KEY `u_document_term` (`document_fk`,`term_fk`),
  KEY `document_fk` (`document_fk`),
  KEY `term_fk` (`term_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_document_terms`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_languages`
--

CREATE TABLE IF NOT EXISTS `ac_languages` (
  `language_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'official name',
  `code` char(2) NOT NULL COMMENT 'i.e. ''en''',
  `code2` char(5) DEFAULT NULL COMMENT 'i.e. ''en_US''',
  `category` varchar(20) DEFAULT NULL COMMENT 'you may want to have different lang lists',
  `is_active` tinyint(1) unsigned DEFAULT '1',
  `sort_order` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `code2` (`code2`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `ac_languages`
--

INSERT INTO `ac_languages` (`language_id`, `name`, `code`, `code2`, `category`, `is_active`, `sort_order`) VALUES
(1, 'Español', 'es', 'es_ES', NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_metadata`
--

CREATE TABLE IF NOT EXISTS `ac_metadata` (
  `metadata_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ns` varchar(64) DEFAULT NULL COMMENT 'namespace i.e. DB.TABLENAME.ID',
  `key` varchar(20) NOT NULL,
  `value` text,
  `category` varchar(20) DEFAULT NULL COMMENT 'i.e. custom_field',
  `language_fk` bigint(20) unsigned DEFAULT NULL,
  `parent_fk` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`metadata_id`),
  KEY `language_fk` (`language_fk`),
  KEY `parent_fk` (`parent_fk`),
  KEY `idx_full` (`ns`,`key`,`language_fk`),
  KEY `idx_ns` (`ns`,`language_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_metadata`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_revisions`
--

CREATE TABLE IF NOT EXISTS `ac_revisions` (
  `revision_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_fk` bigint(20) unsigned NOT NULL,
  `language_fk` bigint(20) unsigned NOT NULL,
  `document_data` longtext COMMENT 'serialized R_Document object',
  `revision_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`revision_id`),
  KEY `document_fk` (`document_fk`),
  KEY `language_fk` (`language_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='documents'' revisions' AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_revisions`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_terms`
--

CREATE TABLE IF NOT EXISTS `ac_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(20) DEFAULT 'tag' COMMENT 'type (category, tag, ...)',
  `subcategory` varchar(20) DEFAULT NULL COMMENT 'subtype',
  `parent_fk` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `parent_fk` (`parent_fk`),
  KEY `idx_full` (`category`,`subcategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='classification / tagging terms' AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_terms`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_termsml`
--

CREATE TABLE IF NOT EXISTS `ac_termsml` (
  `termsml_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_fk` bigint(20) unsigned NOT NULL,
  `language_fk` bigint(20) unsigned NOT NULL,
  `is_translated` tinyint(1) unsigned DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`termsml_id`),
  UNIQUE KEY `u_term_language` (`term_fk`,`language_fk`),
  KEY `term_fk` (`term_fk`),
  KEY `language_fk` (`language_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_termsml`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_tokens`
--

CREATE TABLE IF NOT EXISTS `ac_tokens` (
  `token_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `category` varchar(20) DEFAULT 'auth',
  `user_fk` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `valid_ip` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `token` (`token`,`category`),
  KEY `user_fk` (`user_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='access tokens for various purposes' AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `ac_tokens`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ac_users`
--

CREATE TABLE IF NOT EXISTS `ac_users` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `screenname` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `secret` varchar(128) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) unsigned DEFAULT '0',
  `group` int(4) DEFAULT '1002' COMMENT 'i.e: 1000=root, 1001=administrators, 1002=users, etc',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Volcar la base de datos para la tabla `ac_users`
--

INSERT INTO `ac_users` (`user_id`, `firstname`, `lastname`, `screenname`, `email`, `secret`, `created_at`, `is_active`, `group`) VALUES
(1, NULL, NULL, 'Desarrollador', 'team@anid.es', '-', '2012-02-23 18:44:13', 1, 1000),
(2, NULL, NULL, 'Administrador', 'admin@anid.es', '-', '2012-02-23 18:44:13', 1, 1001);

--
-- Filtros para las tablas descargadas (dump)
--

--
-- Filtros para la tabla `ac_attachmentsml`
--
ALTER TABLE `ac_attachmentsml`
  ADD CONSTRAINT `ac_attachmentsml_ibfk_1` FOREIGN KEY (`attachment_fk`) REFERENCES `ac_attachments` (`attachment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_attachmentsml_ibfk_2` FOREIGN KEY (`language_fk`) REFERENCES `ac_languages` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_comments`
--
ALTER TABLE `ac_comments`
  ADD CONSTRAINT `ac_comments_ibfk_1` FOREIGN KEY (`language_fk`) REFERENCES `ac_languages` (`language_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_comments_ibfk_2` FOREIGN KEY (`user_fk`) REFERENCES `ac_users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_documents`
--
ALTER TABLE `ac_documents`
  ADD CONSTRAINT `ac_documents_ibfk_1` FOREIGN KEY (`parent_fk`) REFERENCES `ac_documents` (`document_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_documents_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `ac_users` (`user_id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `ac_documents_ibfk_3` FOREIGN KEY (`modified_by`) REFERENCES `ac_users` (`user_id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Filtros para la tabla `ac_documentsml`
--
ALTER TABLE `ac_documentsml`
  ADD CONSTRAINT `ac_documentsml_ibfk_1` FOREIGN KEY (`document_fk`) REFERENCES `ac_documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_documentsml_ibfk_2` FOREIGN KEY (`language_fk`) REFERENCES `ac_languages` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_document_terms`
--
ALTER TABLE `ac_document_terms`
  ADD CONSTRAINT `ac_document_terms_ibfk_1` FOREIGN KEY (`document_fk`) REFERENCES `ac_documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_document_terms_ibfk_2` FOREIGN KEY (`term_fk`) REFERENCES `ac_terms` (`term_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_metadata`
--
ALTER TABLE `ac_metadata`
  ADD CONSTRAINT `ac_metadata_ibfk_2` FOREIGN KEY (`parent_fk`) REFERENCES `ac_metadata` (`metadata_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_metadata_ibfk_3` FOREIGN KEY (`language_fk`) REFERENCES `ac_languages` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_revisions`
--
ALTER TABLE `ac_revisions`
  ADD CONSTRAINT `ac_revisions_ibfk_1` FOREIGN KEY (`document_fk`) REFERENCES `ac_documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_revisions_ibfk_2` FOREIGN KEY (`language_fk`) REFERENCES `ac_languages` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_terms`
--
ALTER TABLE `ac_terms`
  ADD CONSTRAINT `ac_terms_ibfk_1` FOREIGN KEY (`parent_fk`) REFERENCES `ac_terms` (`term_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_termsml`
--
ALTER TABLE `ac_termsml`
  ADD CONSTRAINT `ac_termsml_ibfk_1` FOREIGN KEY (`term_fk`) REFERENCES `ac_terms` (`term_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ac_termsml_ibfk_2` FOREIGN KEY (`language_fk`) REFERENCES `ac_languages` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ac_tokens`
--
ALTER TABLE `ac_tokens`
  ADD CONSTRAINT `ac_tokens_ibfk_1` FOREIGN KEY (`user_fk`) REFERENCES `ac_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
