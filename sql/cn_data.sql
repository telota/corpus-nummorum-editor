-- MySQL dump 10.13  Distrib 8.0.27, for Linux (aarch64)
--
-- Host: localhost    Database: cn_data
-- ------------------------------------------------------
-- Server version	8.0.27-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `data_authorities`
--

DROP TABLE IF EXISTS `data_authorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_authorities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `authority_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `authority_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authority_de` (`authority_de`),
  KEY `fk_data_authorities_creator_idx` (`id_creator`),
  KEY `fk_data_authorities_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_authorities_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_authorities_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins`
--

DROP TABLE IF EXISTS `data_coins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '- this table ist the old table "thrakien.IndividualCoins"',
  `description_private` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `die_combination` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diameter_max` decimal(5,2) DEFAULT NULL,
  `diameter_min` decimal(5,2) DEFAULT NULL,
  `diameter_ignore` tinyint DEFAULT '0',
  `weight` decimal(5,2) DEFAULT NULL,
  `weight_ignore` tinyint DEFAULT '0',
  `axis` tinyint DEFAULT NULL,
  `countermark_o_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `countermark_r_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `countermark_o_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `countermark_r_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `undertype_o_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `undertype_r_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `undertype_o_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `undertype_r_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_mint` int DEFAULT NULL,
  `id_findspot` int DEFAULT NULL,
  `id_hoard` int DEFAULT NULL,
  `plastercast_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provenience` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_owner_original` int DEFAULT NULL,
  `collection_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_owner_reproduction` int DEFAULT NULL,
  `owner_is_unsure` tinyint NOT NULL DEFAULT '0',
  `has_centerhole` tinyint NOT NULL DEFAULT '0',
  `source_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_authority_person` int DEFAULT NULL,
  `id_authority_group` int DEFAULT NULL,
  `id_authority` int DEFAULT NULL,
  `id_material` int DEFAULT NULL,
  `id_denomination` int DEFAULT NULL,
  `id_standard` int DEFAULT NULL,
  `date_start` decimal(4,0) DEFAULT NULL,
  `date_end` decimal(4,0) DEFAULT NULL,
  `date_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_period` int DEFAULT NULL,
  `id_issuer` int DEFAULT NULL,
  `id_die_o` int DEFAULT NULL,
  `id_die_r` int DEFAULT NULL,
  `die_o_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `die_r_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_legend_o` int DEFAULT NULL,
  `id_legend_r` int DEFAULT NULL,
  `id_design_o` int DEFAULT NULL,
  `id_design_r` int DEFAULT NULL,
  `is_forgery` tinyint NOT NULL DEFAULT '0',
  `description_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '- you could find the start value in data_coins_submitted ',
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '- you could find the start value in data_coins_submitted ',
  `pecularities_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pecularities_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment_private` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment_public` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment_public_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `same_as_coin_external` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publication_state` tinyint NOT NULL DEFAULT '0',
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `index_fk_findspots` (`id_findspot`),
  KEY `index_fk_hoards` (`id_hoard`),
  KEY `index_fk_mints` (`id_mint`),
  KEY `fk_data_coins_1_idx` (`id_editor`),
  KEY `fk_data_coins_creator_idx` (`id_creator`),
  KEY `fk_data_coins_material_idx` (`id_material`),
  KEY `fk_data_coins_authotities_idx` (`id_authority`),
  KEY `fk_data_coins_period_idx` (`id_period`),
  KEY `fk_data_coins_authority_group_idx` (`id_authority_group`),
  KEY `fk_data_coins_denominations_idx` (`id_denomination`),
  KEY `fk_data_coins_standard_idx` (`id_standard`),
  KEY `fk_data_coins_design_o_idx` (`id_design_o`),
  KEY `fk_data_coins_design_r_idx` (`id_design_r`),
  KEY `fk_data_coins_die_o_idx` (`id_die_o`),
  KEY `fk_data_coins_die_r_idx` (`id_die_r`),
  KEY `fk_data_coins_legend_o_idx` (`id_legend_o`),
  KEY `fk_data_coins_legend_r_idx` (`id_legend_r`),
  KEY `fk_data_coins_authority_person_idx` (`id_authority_person`),
  KEY `fk_data_coins_issuer_idx` (`id_issuer`),
  KEY `fk_data_coins_owner_idx` (`id_owner_original`),
  KEY `fk_data_coins_owner_reproduction_idx` (`id_owner_reproduction`),
  CONSTRAINT `fk_data_coins_authority_group` FOREIGN KEY (`id_authority_group`) REFERENCES `data_tribes` (`id`),
  CONSTRAINT `fk_data_coins_authority_person` FOREIGN KEY (`id_authority_person`) REFERENCES `data_persons` (`id`),
  CONSTRAINT `fk_data_coins_authotities` FOREIGN KEY (`id_authority`) REFERENCES `data_authorities` (`id`),
  CONSTRAINT `fk_data_coins_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_denomination` FOREIGN KEY (`id_denomination`) REFERENCES `data_denominations` (`id`),
  CONSTRAINT `fk_data_coins_design_o` FOREIGN KEY (`id_design_o`) REFERENCES `data_designs` (`id`),
  CONSTRAINT `fk_data_coins_design_r` FOREIGN KEY (`id_design_r`) REFERENCES `data_designs` (`id`),
  CONSTRAINT `fk_data_coins_die_o` FOREIGN KEY (`id_die_o`) REFERENCES `data_dies` (`id`),
  CONSTRAINT `fk_data_coins_die_r` FOREIGN KEY (`id_die_r`) REFERENCES `data_dies` (`id`),
  CONSTRAINT `fk_data_coins_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_findspots` FOREIGN KEY (`id_findspot`) REFERENCES `data_findspots` (`id`),
  CONSTRAINT `fk_data_coins_hoards` FOREIGN KEY (`id_hoard`) REFERENCES `data_hoards` (`id`),
  CONSTRAINT `fk_data_coins_issuer` FOREIGN KEY (`id_issuer`) REFERENCES `data_persons` (`id`),
  CONSTRAINT `fk_data_coins_legend_o` FOREIGN KEY (`id_legend_o`) REFERENCES `data_legends` (`id`),
  CONSTRAINT `fk_data_coins_legend_r` FOREIGN KEY (`id_legend_r`) REFERENCES `data_legends` (`id`),
  CONSTRAINT `fk_data_coins_material` FOREIGN KEY (`id_material`) REFERENCES `data_materials` (`id`),
  CONSTRAINT `fk_data_coins_mints` FOREIGN KEY (`id_mint`) REFERENCES `data_mints` (`id`),
  CONSTRAINT `fk_data_coins_owner` FOREIGN KEY (`id_owner_original`) REFERENCES `data_owners` (`id`),
  CONSTRAINT `fk_data_coins_owner_reproduction` FOREIGN KEY (`id_owner_reproduction`) REFERENCES `data_owners` (`id`),
  CONSTRAINT `fk_data_coins_period` FOREIGN KEY (`id_period`) REFERENCES `data_periods` (`id`),
  CONSTRAINT `fk_data_coins_standard` FOREIGN KEY (`id_standard`) REFERENCES `data_standards` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46435 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_imagesets`
--

DROP TABLE IF EXISTS `data_coins_imagesets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_imagesets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_coin` int DEFAULT NULL,
  `id_duplicate_coin` int DEFAULT NULL,
  `id_image_obverse` int DEFAULT NULL,
  `id_image_reverse` int DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_coins_imagesets_coins_idx` (`id_coin`),
  KEY `fk_data_coins_imagesets_duplicate_coin_idx` (`id_duplicate_coin`),
  KEY `fk_data_coins_imagesets_image_obverse_idx` (`id_image_obverse`),
  KEY `fk_data_coins_imagesets_image_reverse_idx` (`id_image_reverse`),
  KEY `fk_data_coins_imagesets_editor_idx` (`id_editor`),
  KEY `fk_data_coins_imagesets_creator_idx` (`id_creator`),
  CONSTRAINT `fk_data_coins_imagesets_coins` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_imagesets_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_imagesets_duplicate_coin` FOREIGN KEY (`id_duplicate_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_imagesets_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_imagesets_image_obverse` FOREIGN KEY (`id_image_obverse`) REFERENCES `data_images` (`id`),
  CONSTRAINT `fk_data_coins_imagesets_image_reverse` FOREIGN KEY (`id_image_reverse`) REFERENCES `data_images` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_submitted`
--

DROP TABLE IF EXISTS `data_coins_submitted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_submitted` (
  `id` int NOT NULL,
  `legend_o_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `legend_r_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `legend_o_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `legend_r_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `design_o` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `design_r` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `citations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `literature` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `import` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`id`),
  CONSTRAINT `fk_data_coins_submitted_id` FOREIGN KEY (`id`) REFERENCES `data_coins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_controlmarks`
--

DROP TABLE IF EXISTS `data_coins_to_controlmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_controlmarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_coin` int NOT NULL,
  `id_controlmark` int NOT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `number` tinyint NOT NULL DEFAULT '1',
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`id_coin`,`id_controlmark`,`side`,`number`),
  KEY `fk_data_coins_to_controlmarks_coin_idx` (`id_coin`),
  KEY `fk_data_coins_to_controlmarks_controlmark_idx` (`id_controlmark`),
  KEY `fk_data_coins_to_controlmarks_creator_idx` (`id_creator`),
  KEY `fk_data_coins_to_controlmarks_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_coins_to_controlmarks_coin` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_controlmarks_controlmark` FOREIGN KEY (`id_controlmark`) REFERENCES `data_controlmarks` (`id`),
  CONSTRAINT `fk_data_coins_to_controlmarks_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_to_controlmarks_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_monograms`
--

DROP TABLE IF EXISTS `data_coins_to_monograms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_monograms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_monogram` int NOT NULL,
  `id_coin` int NOT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `id_position` int DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index5` (`id_monogram`,`id_coin`,`side`,`id_position`),
  KEY `fk_data_coins_to_monograms_monogram_idx` (`id_monogram`),
  KEY `fk_data_coins_to_monograms_coin_idx` (`id_coin`),
  KEY `fk_data_coins_to_monograms_position_idx` (`id_position`),
  KEY `fk_data_coins_to_monograms_creator_idx` (`id_creator`),
  KEY `fk_data_coins_to_monograms_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_coins_to_monograms_coin` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_monograms_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_to_monograms_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_to_monograms_monogram` FOREIGN KEY (`id_monogram`) REFERENCES `data_monograms` (`id`),
  CONSTRAINT `fk_data_coins_to_monograms_position` FOREIGN KEY (`id_position`) REFERENCES `data_positions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102227 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_objectgroups`
--

DROP TABLE IF EXISTS `data_coins_to_objectgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_objectgroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_coin` int DEFAULT NULL,
  `id_objectgroup` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`id_coin`,`id_objectgroup`),
  KEY `fk_data_coins_to_emissions_1_idx` (`id_coin`),
  KEY `fk_data_coins_to_emissions_emission_idx` (`id_objectgroup`),
  CONSTRAINT `fk_data_coins_to_emissions_coin` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_emissions_emission` FOREIGN KEY (`id_objectgroup`) REFERENCES `data_objectgroups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_persons`
--

DROP TABLE IF EXISTS `data_coins_to_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_persons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_coin` int DEFAULT NULL,
  `id_person` int DEFAULT NULL,
  `id_function` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index3` (`id_coin`,`id_person`,`id_function`),
  KEY `fk_data_coins_to_persons_idx` (`id_coin`),
  KEY `fk_data_coins_to_persons_persons_idx` (`id_person`),
  KEY `fk_data_coins_to_persons_functions_idx` (`id_function`),
  CONSTRAINT `fk_data_coins_to_persons_coins` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_persons_functions` FOREIGN KEY (`id_function`) REFERENCES `data_persons_functions` (`id`),
  CONSTRAINT `fk_data_coins_to_persons_persons` FOREIGN KEY (`id_person`) REFERENCES `data_persons` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44463 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_symbols`
--

DROP TABLE IF EXISTS `data_coins_to_symbols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_symbols` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_symbol` int NOT NULL,
  `id_coin` int NOT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `id_position` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index5` (`id_symbol`,`id_coin`,`side`,`id_position`),
  KEY `fk_data_coins_to_symbols_position_idx` (`id_position`),
  KEY `fk_data_coins_to_symbols_coin_idx` (`id_coin`),
  KEY `fk_data_coins_to_symbols_symbol_idx` (`id_symbol`),
  CONSTRAINT `fk_data_coins_to_symbols_coin` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_symbols_position` FOREIGN KEY (`id_position`) REFERENCES `data_positions` (`id`),
  CONSTRAINT `fk_data_coins_to_symbols_symbol` FOREIGN KEY (`id_symbol`) REFERENCES `data_symbols` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=213332 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_types`
--

DROP TABLE IF EXISTS `data_coins_to_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_coin` int DEFAULT NULL,
  `id_type` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coin_to_type` (`id_coin`,`id_type`),
  KEY `fk_data_coins_to_types_coins_idx` (`id_coin`),
  KEY `fk_data_coins_to_types_types_idx` (`id_type`),
  CONSTRAINT `fk_data_coins_to_types_coins` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_types_types` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34811 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_types_inherit`
--

DROP TABLE IF EXISTS `data_coins_to_types_inherit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_types_inherit` (
  `id` int NOT NULL,
  `id_type` int DEFAULT NULL,
  `date_inherited` tinyint DEFAULT '0',
  `period_inherited` tinyint DEFAULT '0',
  `legend_o_inherited` tinyint DEFAULT '0',
  `symbol_o_inherited` tinyint DEFAULT '0',
  `design_o_inherited` tinyint DEFAULT '0',
  `monogram_o_inherited` tinyint DEFAULT '0',
  `legend_r_inherited` tinyint DEFAULT '0',
  `symbol_r_inherited` tinyint DEFAULT '0',
  `design_r_inherited` tinyint DEFAULT '0',
  `monogram_r_inherited` tinyint DEFAULT '0',
  `issuer_inherited` tinyint DEFAULT '0',
  `authority_inherited` tinyint DEFAULT '0',
  `authority_person_inherited` tinyint DEFAULT '0',
  `authority_group_inherited` tinyint DEFAULT '0',
  `mint_inherited` tinyint DEFAULT '0',
  `material_inherited` tinyint DEFAULT '0',
  `standard_inherited` tinyint DEFAULT '0',
  `denomination_inherited` tinyint DEFAULT '0',
  `person_inherit` tinyint DEFAULT '0',
  `comment_private_inherited` tinyint DEFAULT '0',
  `comment_public_inherited` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_coins_to_type_type_idx` (`id_type`),
  CONSTRAINT `fk_data_coins_to_type_id` FOREIGN KEY (`id`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_type_type` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_weblinks`
--

DROP TABLE IF EXISTS `data_coins_to_weblinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_weblinks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_coin` int DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semantics` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_coins_to_weblinks_1_idx` (`id_coin`),
  CONSTRAINT `fk_data_coins_to_weblinks_1` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=697 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_coins_to_zotero`
--

DROP TABLE IF EXISTS `data_coins_to_zotero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_coins_to_zotero` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_coin` int DEFAULT NULL,
  `zotero_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annotation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `this_coin` tinyint NOT NULL DEFAULT '0',
  `comment_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_coins_to_zotero_coin_idx` (`id_coin`),
  KEY `fk_data_coins_to_zotero_editor_idx` (`id_editor`),
  KEY `fk_data_coins_to_zotero_creator_idx` (`id_creator`),
  CONSTRAINT `fk_data_coins_to_zotero_coin` FOREIGN KEY (`id_coin`) REFERENCES `data_coins` (`id`),
  CONSTRAINT `fk_data_coins_to_zotero_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_coins_to_zotero_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87958 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_controlmarks`
--

DROP TABLE IF EXISTS `data_controlmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_controlmarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `controlmark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `controlmark` (`controlmark`),
  KEY `fk_data_controlmarks_creator_idx` (`id_creator`),
  KEY `fk_data_controlmarks_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_controlmarks_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_controlmarks_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_copyrights`
--

DROP TABLE IF EXISTS `data_copyrights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_copyrights` (
  `id` int NOT NULL AUTO_INCREMENT,
  `copyright` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `copyright` (`copyright`),
  KEY `fk_data_copyrights_creator_idx` (`id_creator`),
  KEY `fk_data_copyrights_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_copyrights_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_copyrights_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_countries_iso3166`
--

DROP TABLE IF EXISTS `data_countries_iso3166`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_countries_iso3166` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_iso3166` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `en` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `de` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fr` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `it` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ru` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index2` (`id_iso3166`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_denominations`
--

DROP TABLE IF EXISTS `data_denominations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_denominations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `denomination_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `denomination_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_denominations_creator_idx` (`id_creator`),
  KEY `fk_data_denominations_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_denominations_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_denominations_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_designs`
--

DROP TABLE IF EXISTS `data_designs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_designs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `design_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `design_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `design_bg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `side` tinyint NOT NULL DEFAULT '0',
  `role` tinyint NOT NULL DEFAULT '0',
  `border_of_dots` tinyint NOT NULL DEFAULT '0',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_designs_creator_idx` (`id_creator`),
  KEY `fk_data_designs_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_designs_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_designs_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8477 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_dies`
--

DROP TABLE IF EXISTS `data_dies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_dies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_legend` int DEFAULT NULL,
  `id_design` int DEFAULT NULL,
  `name_die` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_dies_creator_idx` (`id_creator`),
  KEY `fk_data_dies_editor_idx` (`id_editor`),
  KEY `fk_data_dies_legend_idx` (`id_legend`),
  KEY `fk_data_dies_design_idx` (`id_design`),
  CONSTRAINT `fk_data_dies_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_dies_design` FOREIGN KEY (`id_design`) REFERENCES `data_designs` (`id`),
  CONSTRAINT `fk_data_dies_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_dies_legend` FOREIGN KEY (`id_legend`) REFERENCES `data_legends` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_dies_to_monograms`
--

DROP TABLE IF EXISTS `data_dies_to_monograms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_dies_to_monograms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_die` int NOT NULL,
  `id_monogram` int NOT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `id_position` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`id_die`,`id_monogram`,`side`,`id_position`),
  KEY `fk_data_dies_to_monograms_monogram_idx` (`id_monogram`),
  KEY `fk_data_dies_to_monograms_die_idx` (`id_die`),
  KEY `fk_data_dies_to_monograms_position_idx` (`id_position`),
  CONSTRAINT `fk_data_dies_to_monograms_die` FOREIGN KEY (`id_die`) REFERENCES `data_dies` (`id`),
  CONSTRAINT `fk_data_dies_to_monograms_monogram` FOREIGN KEY (`id_monogram`) REFERENCES `data_monograms` (`id`),
  CONSTRAINT `fk_data_dies_to_monograms_position` FOREIGN KEY (`id_position`) REFERENCES `data_positions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=506 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_dies_to_symbols`
--

DROP TABLE IF EXISTS `data_dies_to_symbols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_dies_to_symbols` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_die` int NOT NULL,
  `id_symbol` int NOT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `id_position` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`id_die`,`id_symbol`,`side`,`id_position`),
  KEY `fk_data_dies_to_symbols_symbol_idx` (`id_symbol`),
  KEY `fk_data_dies_to_symbols_die_idx` (`id_die`),
  KEY `fk_data_dies_to_symbols_position_idx` (`id_position`),
  CONSTRAINT `fk_data_dies_to_symbols_die` FOREIGN KEY (`id_die`) REFERENCES `data_dies` (`id`),
  CONSTRAINT `fk_data_dies_to_symbols_position` FOREIGN KEY (`id_position`) REFERENCES `data_positions` (`id`),
  CONSTRAINT `fk_data_dies_to_symbols_symbol` FOREIGN KEY (`id_symbol`) REFERENCES `data_symbols` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_files`
--

DROP TABLE IF EXISTS `data_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `path` varchar(760) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_file_name` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `background_color` char(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index2` (`path`),
  KEY `fk_data_files_1_idx` (`id_creator`),
  CONSTRAINT `fk_data_files_1` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_findspots`
--

DROP TABLE IF EXISTS `data_findspots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_findspots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `findspot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geonames_id` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alias` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Findspot_UNIQUE` (`findspot`),
  KEY `fk_data_findspots_creator_idx` (`id_creator`),
  KEY `fk_data_findspots_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_findspots_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_findspots_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_hoards`
--

DROP TABLE IF EXISTS `data_hoards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_hoards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hoard` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_findspot` int DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hoard_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hoard_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_terminal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hoards_UNIQUE` (`hoard`),
  KEY `fk_data_hoards_1_idx` (`id_findspot`),
  KEY `fk_data_hoards_creator_idx` (`id_creator`),
  KEY `fk_data_hoards_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_hoards_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_hoards_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_hoards_findspots` FOREIGN KEY (`id_findspot`) REFERENCES `data_findspots` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_images`
--

DROP TABLE IF EXISTS `data_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_color` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photographer` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `image_name` (`image`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_legends`
--

DROP TABLE IF EXISTS `data_legends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_legends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `legend` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legend_sort_basis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legend_language` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `id_legend_direction` int DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_type` tinyint NOT NULL DEFAULT '0',
  `fulltext_proposal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`created_at`),
  KEY `fk_data_legends_creator_idx` (`id_creator`),
  KEY `fk_data_legends_editor_idx` (`id_editor`),
  KEY `fk_data_legends_legend_directions_idx` (`id_legend_direction`),
  CONSTRAINT `fk_data_legends_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_legends_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_legends_legend_directions` FOREIGN KEY (`id_legend_direction`) REFERENCES `data_legends_directions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_legends_directions`
--

DROP TABLE IF EXISTS `data_legends_directions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_legends_directions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `legend_direction` tinyint NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `legend_direction` (`legend_direction`),
  KEY `fk_data_legends_directions_creator_idx` (`id_creator`),
  KEY `fk_data_legends_directions_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_legends_directions_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_legends_directions_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_materials`
--

DROP TABLE IF EXISTS `data_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `material_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `material_de` (`material_de`),
  KEY `fk_data_materials_editor1_idx` (`id_editor`),
  KEY `fk_data_materials_creator_idx` (`id_creator`),
  CONSTRAINT `fk_data_materials_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_materials_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_mints`
--

DROP TABLE IF EXISTS `data_mints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_mints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `placename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imported_nomisma_subregion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mints` (`mint`),
  KEY `fk_data_mints_creator_idx` (`id_creator`),
  KEY `fk_data_mints_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_mints_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_mints_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_monograms`
--

DROP TABLE IF EXISTS `data_monograms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_monograms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `monogram` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lettercomb` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_monograms_creator_idx` (`id_creator`),
  KEY `fk_data_monograms_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_monograms_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_monograms_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1034 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_objectgroups`
--

DROP TABLE IF EXISTS `data_objectgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_objectgroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objectgroup` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_emissions_creator_idx` (`id_creator`),
  KEY `fk_data_emissions_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_objectgroups_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_objectgroups_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_owners`
--

DROP TABLE IF EXISTS `data_owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_owners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_name_public` tinyint NOT NULL DEFAULT '0',
  `is_checked` tinyint NOT NULL DEFAULT '0',
  `id_copyright` int DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_owners_copyright_idx` (`id_copyright`),
  KEY `fk_data_owners_creator_idx` (`id_creator`),
  KEY `fk_data_owners_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_owners_copyright` FOREIGN KEY (`id_copyright`) REFERENCES `data_copyrights` (`id`),
  CONSTRAINT `fk_data_owners_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_owners_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=323 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_periods`
--

DROP TABLE IF EXISTS `data_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_periods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `period_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_from` decimal(4,0) DEFAULT NULL,
  `date_to` decimal(4,0) DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `period_de` (`period_de`),
  KEY `fk_data_periods_creator_idx` (`id_creator`),
  KEY `fk_data_periods_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_periods_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_periods_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_persons`
--

DROP TABLE IF EXISTS `data_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_persons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `definition` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` decimal(4,0) DEFAULT NULL,
  `date_end` decimal(4,0) DEFAULT NULL,
  `date_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caesar_start` decimal(4,0) DEFAULT NULL,
  `caesar_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `augustus_start` decimal(4,0) DEFAULT NULL,
  `augustus_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_authority` tinyint NOT NULL DEFAULT '0',
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_persons_creator_idx` (`id_creator`),
  KEY `fk_data_persons_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_persons_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_persons_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1080 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_persons_duplicates`
--

DROP TABLE IF EXISTS `data_persons_duplicates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_persons_duplicates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` decimal(4,0) DEFAULT NULL,
  `date_end` decimal(4,0) DEFAULT NULL,
  `date_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alias` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_authority` tinyint NOT NULL DEFAULT '0',
  `is_delete` tinyint NOT NULL DEFAULT '0',
  `is_alternative` int DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_persons_duplicates_creator_idx` (`id_creator`),
  KEY `fk_data_persons_duplicates_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_persons_duplicates_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_persons_duplicates_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=511 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_persons_functions`
--

DROP TABLE IF EXISTS `data_persons_functions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_persons_functions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `person_function_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_function_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_function_el` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_persons_functions_creator_idx` (`id_creator`),
  KEY `fk_data_persons_functions_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_persons_functions_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_persons_functions_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_photographers`
--

DROP TABLE IF EXISTS `data_photographers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_photographers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `photographer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `photographer` (`photographer`),
  KEY `fk_data_photographers_creator_idx` (`id_creator`),
  KEY `fk_data_photographers_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_photographers_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_photographers_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_positions`
--

DROP TABLE IF EXISTS `data_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_positions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `position_de` (`position_de`),
  KEY `fk_data_positions_creator_idx` (`id_creator`),
  KEY `fk_data_positions_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_positions_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_positions_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_regions`
--

DROP TABLE IF EXISTS `data_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_regions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `region` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `de` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `en` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `el` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bg` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ru` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tr` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ro` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `region_UNIQUE` (`region`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_standards`
--

DROP TABLE IF EXISTS `data_standards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_standards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `standard_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `standard_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `standard_en` (`standard_en`),
  KEY `fk_data_standards_creator_idx` (`id_creator`),
  KEY `fk_data_standards_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_standards_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_standards_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_symbols`
--

DROP TABLE IF EXISTS `data_symbols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_symbols` (
  `id` int NOT NULL AUTO_INCREMENT,
  `symbol_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_symbols_creator_idx` (`id_creator`),
  KEY `fk_data_symbols_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_symbols_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_symbols_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=547 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_tribes`
--

DROP TABLE IF EXISTS `data_tribes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_tribes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tribe_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tribe_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tribe_de` (`tribe_de`),
  KEY `fk_data_tribes_creator_idx` (`id_creator`),
  KEY `fk_data_tribes_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_tribes_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_tribes_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_type_variations`
--

DROP TABLE IF EXISTS `data_type_variations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_type_variations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_type` int DEFAULT NULL,
  `type_variation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1091 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types`
--

DROP TABLE IF EXISTS `data_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_private` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_private` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment_private` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment_public` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `comment_public_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_denomination` int DEFAULT NULL,
  `id_mint` int DEFAULT NULL,
  `id_period` int DEFAULT NULL,
  `id_issuer` int DEFAULT NULL,
  `id_authority` int DEFAULT NULL,
  `id_authority_person` int DEFAULT NULL,
  `id_authority_group` int DEFAULT NULL,
  `id_material` int DEFAULT NULL,
  `id_standard` int DEFAULT NULL,
  `id_design_o` int DEFAULT NULL,
  `id_design_r` int DEFAULT NULL,
  `id_legend_o` int DEFAULT NULL,
  `id_legend_r` int DEFAULT NULL,
  `date_start` decimal(4,0) DEFAULT NULL,
  `date_end` decimal(4,0) DEFAULT NULL,
  `date_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `id_imageset` int DEFAULT NULL,
  `pecularities_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pecularities_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `publication_state` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_types_denomination_idx` (`id_denomination`),
  KEY `fk_data_types_mint_idx` (`id_mint`),
  KEY `fk_data_types_period_idx` (`id_period`),
  KEY `fk_data_types_issuer_idx` (`id_issuer`),
  KEY `fk_data_types_authority_idx` (`id_authority`),
  KEY `fk_data_types_authority_person_idx` (`id_authority_person`),
  KEY `fk_data_types_material_idx` (`id_material`),
  KEY `fk_data_types_standard_idx` (`id_standard`),
  KEY `fk_data_types_design_o_idx` (`id_design_o`),
  KEY `fk_data_types_design_r_idx` (`id_design_r`),
  KEY `fk_data_types_legend_r_idx` (`id_legend_r`),
  KEY `fk_data_types_editor_idx` (`id_editor`),
  KEY `fk_data_types_creator_idx` (`id_creator`),
  KEY `fk_data_types_imagesets_idx` (`id_imageset`),
  KEY `fk_data_types_authority_group_idx` (`id_authority_group`),
  KEY `fk_data_types_legend_o_idx` (`id_legend_o`),
  CONSTRAINT `fk_data_types_authority` FOREIGN KEY (`id_authority`) REFERENCES `data_authorities` (`id`),
  CONSTRAINT `fk_data_types_authority_group` FOREIGN KEY (`id_authority_group`) REFERENCES `data_tribes` (`id`),
  CONSTRAINT `fk_data_types_authority_person` FOREIGN KEY (`id_authority_person`) REFERENCES `data_persons` (`id`),
  CONSTRAINT `fk_data_types_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_types_denomination` FOREIGN KEY (`id_denomination`) REFERENCES `data_denominations` (`id`),
  CONSTRAINT `fk_data_types_design_o` FOREIGN KEY (`id_design_o`) REFERENCES `data_designs` (`id`),
  CONSTRAINT `fk_data_types_design_r` FOREIGN KEY (`id_design_r`) REFERENCES `data_designs` (`id`),
  CONSTRAINT `fk_data_types_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_types_issuer` FOREIGN KEY (`id_issuer`) REFERENCES `data_persons` (`id`),
  CONSTRAINT `fk_data_types_legend_o` FOREIGN KEY (`id_legend_o`) REFERENCES `data_legends` (`id`),
  CONSTRAINT `fk_data_types_legend_r` FOREIGN KEY (`id_legend_r`) REFERENCES `data_legends` (`id`),
  CONSTRAINT `fk_data_types_material` FOREIGN KEY (`id_material`) REFERENCES `data_materials` (`id`),
  CONSTRAINT `fk_data_types_mint` FOREIGN KEY (`id_mint`) REFERENCES `data_mints` (`id`),
  CONSTRAINT `fk_data_types_period` FOREIGN KEY (`id_period`) REFERENCES `data_periods` (`id`),
  CONSTRAINT `fk_data_types_standard` FOREIGN KEY (`id_standard`) REFERENCES `data_standards` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_submitted`
--

DROP TABLE IF EXISTS `data_types_submitted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_submitted` (
  `id` int NOT NULL,
  `legend_o_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `legend_r_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `legend_o_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `legend_r_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `design_o` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `design_r` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_de` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `citations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `literature` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `import` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`id`),
  CONSTRAINT `fk_data_types_submitted_id` FOREIGN KEY (`id`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_findspots`
--

DROP TABLE IF EXISTS `data_types_to_findspots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_findspots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_type` int NOT NULL,
  `id_findspot` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`id_type`,`id_findspot`),
  KEY `fk_data_types_to_findspots_type_idx` (`id_type`),
  KEY `fk_data_types_to_findspots_findspot_idx` (`id_findspot`),
  CONSTRAINT `fk_data_types_to_findspots_findspot` FOREIGN KEY (`id_findspot`) REFERENCES `data_findspots` (`id`),
  CONSTRAINT `fk_data_types_to_findspots_type` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_hoards`
--

DROP TABLE IF EXISTS `data_types_to_hoards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_hoards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_type` int NOT NULL,
  `id_hoard` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`id_type`,`id_hoard`),
  KEY `fk_data_types_to_hoards_type_idx` (`id_type`),
  KEY `fk_data_types_to_hoards_hoard_idx` (`id_hoard`),
  CONSTRAINT `fk_data_types_to_hoards_hoard` FOREIGN KEY (`id_hoard`) REFERENCES `data_hoards` (`id`),
  CONSTRAINT `fk_data_types_to_hoards_type` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_monograms`
--

DROP TABLE IF EXISTS `data_types_to_monograms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_monograms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_monogram` int NOT NULL,
  `id_type` int NOT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `id_position` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index5` (`id_monogram`,`id_type`,`side`,`id_position`),
  KEY `fk_data_types_to_monograms_monogram_idx` (`id_monogram`),
  KEY `fk_data_types_to_monograms_type_idx` (`id_type`),
  KEY `fk_data_types_to_monograms_position_idx` (`id_position`),
  CONSTRAINT `fk_data_types_to_monograms_monogram` FOREIGN KEY (`id_monogram`) REFERENCES `data_monograms` (`id`),
  CONSTRAINT `fk_data_types_to_monograms_position` FOREIGN KEY (`id_position`) REFERENCES `data_positions` (`id`),
  CONSTRAINT `fk_data_types_to_monograms_type` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3680 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_objectgroups`
--

DROP TABLE IF EXISTS `data_types_to_objectgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_objectgroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_type` int DEFAULT NULL,
  `id_objectgroup` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index4` (`id_type`,`id_objectgroup`),
  KEY `fk_data_types_to_emissions_1_idx` (`id_type`),
  KEY `fk_data_types_to_emissions_idx` (`id_objectgroup`),
  CONSTRAINT `fk_data_types_to_emissions_emission` FOREIGN KEY (`id_objectgroup`) REFERENCES `data_objectgroups` (`id`),
  CONSTRAINT `fk_data_types_to_emissions_type` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11680 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_persons`
--

DROP TABLE IF EXISTS `data_types_to_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_persons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_type` int DEFAULT NULL,
  `id_person` int DEFAULT NULL,
  `id_function` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index3` (`id_type`,`id_person`,`id_function`),
  KEY `fk_data_types_to_persons_idx` (`id_type`),
  KEY `fk_data_types_to_persons_persons_idx` (`id_person`),
  KEY `fk_data_types_to_persons_person_functions_idx` (`id_function`),
  CONSTRAINT `fk_data_types_to_persons_person_functions` FOREIGN KEY (`id_function`) REFERENCES `data_persons_functions` (`id`),
  CONSTRAINT `fk_data_types_to_persons_persons` FOREIGN KEY (`id_person`) REFERENCES `data_persons` (`id`),
  CONSTRAINT `fk_data_types_to_persons_types` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27036 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_symbols`
--

DROP TABLE IF EXISTS `data_types_to_symbols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_symbols` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_symbol` int NOT NULL,
  `id_type` int NOT NULL,
  `side` tinyint NOT NULL DEFAULT '0',
  `id_position` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index5` (`id_symbol`,`id_type`,`side`,`id_position`),
  KEY `fk_data_types_to_symbols_type_idx` (`id_type`),
  KEY `fk_data_types_to_symbols_position_idx` (`id_position`),
  KEY `fk_data_types_to_symbols_symbol_idx` (`id_symbol`),
  CONSTRAINT `fk_data_types_to_symbols_position` FOREIGN KEY (`id_position`) REFERENCES `data_positions` (`id`),
  CONSTRAINT `fk_data_types_to_symbols_symbol` FOREIGN KEY (`id_symbol`) REFERENCES `data_symbols` (`id`),
  CONSTRAINT `fk_data_types_to_symbols_type` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8406 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_weblinks`
--

DROP TABLE IF EXISTS `data_types_to_weblinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_weblinks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_type` int DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semantics` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_types_to_weblinks_types1_idx` (`id_type`),
  CONSTRAINT `fk_data_types_to_weblinks_types` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11310 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_types_to_zotero`
--

DROP TABLE IF EXISTS `data_types_to_zotero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_types_to_zotero` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_type` int DEFAULT NULL,
  `zotero_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annotation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `this_type` tinyint NOT NULL DEFAULT '0',
  `comment_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_types_to_zotero_coin_idx` (`id_type`),
  KEY `fk_data_types_to_zotero_editor_idx` (`id_editor`),
  KEY `fk_data_types_to_zotero_creator_idx` (`id_creator`),
  CONSTRAINT `fk_data_types_to_zotero_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_types_to_zotero_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_types_to_zotero_type` FOREIGN KEY (`id_type`) REFERENCES `data_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46700 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_typology`
--

DROP TABLE IF EXISTS `data_typology`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_typology` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mint` int NOT NULL,
  `author` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bibliography` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `links` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_creator` bigint DEFAULT NULL,
  `id_editor` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_mints_typology_mints_idx` (`id_mint`),
  KEY `fk_data_typology_creator_idx` (`id_creator`),
  KEY `fk_data_typology_editor_idx` (`id_editor`),
  CONSTRAINT `fk_data_mints_typology_mints` FOREIGN KEY (`id_mint`) REFERENCES `data_mints` (`id`),
  CONSTRAINT `fk_data_typology_creator` FOREIGN KEY (`id_creator`) REFERENCES `cn_app`.`app_editor_users` (`id`),
  CONSTRAINT `fk_data_typology_editor` FOREIGN KEY (`id_editor`) REFERENCES `cn_app`.`app_editor_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_typology_text`
--

DROP TABLE IF EXISTS `data_typology_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_typology_text` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_typology` int NOT NULL,
  `language` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section_topography` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_research` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_typology` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_metrology` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_chronology` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_special` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_classic` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_hellenistic` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `section_imperial` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_data_nomisma_mint_typology_idx` (`id_typology`),
  CONSTRAINT `fk_data_nomisma_mints_typology` FOREIGN KEY (`id_typology`) REFERENCES `data_typology` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_typology_to_mints`
--

DROP TABLE IF EXISTS `data_typology_to_mints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_typology_to_mints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_typology` int NOT NULL,
  `id_mint` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index5` (`id_typology`,`id_mint`),
  KEY `fk_data_typology_to_mints_typology_idx` (`id_typology`),
  KEY `fk_data_typology_to_mints_mint_idx` (`id_mint`),
  CONSTRAINT `fk_data_typology_to_mints_mint` FOREIGN KEY (`id_mint`) REFERENCES `data_mints` (`id`),
  CONSTRAINT `fk_data_typology_to_mints_typology` FOREIGN KEY (`id_typology`) REFERENCES `data_typology` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_typology_to_nomisma`
--

DROP TABLE IF EXISTS `data_typology_to_nomisma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_typology_to_nomisma` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_typology` int DEFAULT NULL,
  `entity` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomisma_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index2` (`id_typology`,`nomisma_id`),
  CONSTRAINT `fk_data_typology_to_nomisma_1` FOREIGN KEY (`id_typology`) REFERENCES `data_typology` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_typology_to_persons`
--

DROP TABLE IF EXISTS `data_typology_to_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_typology_to_persons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_typology` int NOT NULL,
  `id_person` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index5` (`id_typology`,`id_person`),
  KEY `fk_data_typology_to_persons_typology_idx` (`id_typology`),
  KEY `fk_data_typology_to_persons_person_idx` (`id_person`),
  CONSTRAINT `fk_data_typology_to_persons_person` FOREIGN KEY (`id_person`) REFERENCES `data_persons` (`id`),
  CONSTRAINT `fk_data_typology_to_persons_typology` FOREIGN KEY (`id_typology`) REFERENCES `data_typology` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `index_coins`
--

DROP TABLE IF EXISTS `index_coins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `index_coins` (
  `id` int NOT NULL,
  `data_string` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `index_coins_fulltext` (`data_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `index_types`
--

DROP TABLE IF EXISTS `index_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `index_types` (
  `id` int NOT NULL,
  `data_string` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `index_types_fulltext` (`data_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lgpn_names`
--

DROP TABLE IF EXISTS `lgpn_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lgpn_names` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `letters` varchar(255) DEFAULT NULL,
  `variants` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_from` int DEFAULT NULL,
  `date_to` int DEFAULT NULL,
  `occurances` int DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38700 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nomisma_mints`
--

DROP TABLE IF EXISTS `nomisma_mints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomisma_mints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomisma_id_mint` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomisma_id_region` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mint_nomisma` (`nomisma_id_mint`),
  KEY `index_mints` (`nomisma_id_mint`)
) ENGINE=InnoDB AUTO_INCREMENT=1363 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nomisma_mints_text`
--

DROP TABLE IF EXISTS `nomisma_mints_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomisma_mints_text` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomisma_id_mint` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mint_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mint_definition` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mint_nomismaandlanguage` (`nomisma_id_mint`,`language`)
) ENGINE=InnoDB AUTO_INCREMENT=10569 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nomisma_subregions_to_regions`
--

DROP TABLE IF EXISTS `nomisma_subregions_to_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomisma_subregions_to_regions` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'regions: 1 - thrace, 2 - moesia, 3 - mysia, 4 - troas',
  `nomisma_id_region` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_region` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_nomisma_region` (`nomisma_id_region`),
  KEY `fk_data_nomisma_regions_helper_region_idx` (`id_region`),
  CONSTRAINT `fk_data_nomisma_regions_helper_region` FOREIGN KEY (`id_region`) REFERENCES `data_regions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thrakien_images`
--

DROP TABLE IF EXISTS `thrakien_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thrakien_images` (
  `ImageID` int NOT NULL AUTO_INCREMENT,
  `CoinID` int DEFAULT NULL,
  `DuplicateEntryID` int DEFAULT NULL,
  `ObversePhotographer` varchar(255) DEFAULT NULL,
  `ReversePhotographer` varchar(255) DEFAULT NULL,
  `ObverseImageFilename` text,
  `ReverseImageFilename` text,
  `Path` varchar(255) DEFAULT NULL,
  `BackgroundColor` varchar(255) DEFAULT NULL,
  `ObjectType` enum('plastercast','original') DEFAULT NULL,
  `photo_owner` varchar(255) DEFAULT NULL,
  `Editor` varchar(45) DEFAULT NULL,
  `ChangeDate` varchar(45) DEFAULT NULL,
  `Creator` varchar(45) DEFAULT NULL,
  `CreateDate` varchar(45) DEFAULT NULL,
  `is_private` tinyint NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ImageID`),
  KEY `CoinID` (`CoinID`)
) ENGINE=InnoDB AUTO_INCREMENT=51822 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remote` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_level` tinyint NOT NULL DEFAULT '0',
  `externaluser_lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `externaluser_firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `externaluser_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `externaluser_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `externaluser_termsAgreed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=532 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zotero_import`
--

DROP TABLE IF EXISTS `zotero_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zotero_import` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zotero_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `year_published` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `shorttitle` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `series` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `volume` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edition` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publisher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_trash` tinyint DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fetched_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zotero_id` (`zotero_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35374 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-01-27 11:55:41
