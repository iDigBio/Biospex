ALTER TABLE `actors`
	CHANGE COLUMN `title` `title` VARCHAR(60) NULL DEFAULT NULL AFTER `id`,
	CHANGE COLUMN `url` `url` VARCHAR(255) NULL DEFAULT NULL AFTER `title`,
	CHANGE COLUMN `class` `class` VARCHAR(30) NULL DEFAULT NULL AFTER `url`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `private`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
	CHANGE COLUMN `deleted_at` `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;
ALTER TABLE `actors`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `private`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
ALTER TABLE `actors`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `private`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `actor_contacts`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `email`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
ALTER TABLE `actor_contacts`
	CHANGE COLUMN `email` `email` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `actor_id`;
ALTER TABLE `actor_contacts`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `email`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `actor_expedition`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `order`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
ALTER TABLE `actor_expedition`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `order`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `amcharts`
	CHANGE COLUMN `data` `data` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `project_id`,
	CHANGE COLUMN `raw` `raw` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `data`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `queued`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
ALTER TABLE `amcharts`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `queued`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `downloads`
	CHANGE COLUMN `file` `file` VARCHAR(255) NULL DEFAULT NULL AFTER `actor_id`,
	CHANGE COLUMN `type` `type` ENUM('export','classifications','transcriptions','reconciled','summary') NULL DEFAULT NULL AFTER `file`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `count`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `expeditions`
	CHANGE COLUMN `title` `title` VARCHAR(255) NULL DEFAULT NULL AFTER `project_id`,
	CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL AFTER `title`,
	CHANGE COLUMN `keywords` `keywords` VARCHAR(255) NULL DEFAULT NULL AFTER `description`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `keywords`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `expedition_stats`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `percent_completed`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `failed_jobs`
	CHANGE COLUMN `connection` `connection` TEXT NULL DEFAULT NULL AFTER `id`,
	CHANGE COLUMN `queue` `queue` TEXT NULL DEFAULT NULL AFTER `connection`,
	CHANGE COLUMN `payload` `payload` TEXT NULL DEFAULT NULL AFTER `queue`,
	CHANGE COLUMN `exception` `exception` LONGTEXT NULL DEFAULT NULL AFTER `payload`,
	CHANGE COLUMN `failed_at` `failed_at` TIMESTAMP NULL DEFAULT NULL AFTER `exception`;

ALTER TABLE `faqs`
	CHANGE COLUMN `question` `question` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `faq_category_id`,
	CHANGE COLUMN `answer` `answer` VARCHAR(5000) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `question`;

ALTER TABLE `faq_categories`
	CHANGE COLUMN `name` `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `id`;

ALTER TABLE `groups`
	ALTER `created_at` DROP DEFAULT;
ALTER TABLE `groups`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL AFTER `title`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `headers`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `header`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `imports`
	CHANGE COLUMN `file` `file` VARCHAR(255) NULL DEFAULT NULL AFTER `project_id`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `error`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `invites`
	CHANGE COLUMN `email` `email` VARCHAR(255) NULL DEFAULT NULL AFTER `group_id`,
	CHANGE COLUMN `code` `code` VARCHAR(255) NULL DEFAULT NULL AFTER `email`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `code`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `ltm_translations`
	CHANGE COLUMN `locale` `locale` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `status`,
	CHANGE COLUMN `group` `group` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `locale`,
	CHANGE COLUMN `key` `key` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `group`;

ALTER TABLE `metas`
	CHANGE COLUMN `xml` `xml` BLOB NULL DEFAULT NULL AFTER `project_id`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `xml`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `nfn_workflows`
	CHANGE COLUMN `subject_sets` `subject_sets` TEXT NULL COLLATE 'utf8_unicode_ci' AFTER `workflow`;

ALTER TABLE `notifications`
	CHANGE COLUMN `title` `title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `user_id`,
	CHANGE COLUMN `message` `message` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `title`;

ALTER TABLE `ocr_csv`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `subjects`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `ocr_queues`
	CHANGE COLUMN `status` `status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `data`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `attachments`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `password_resets`
	ALTER `token` DROP DEFAULT;
ALTER TABLE `password_resets`
	CHANGE COLUMN `token` `token` VARCHAR(255) NOT NULL AFTER `email`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `token`;

ALTER TABLE `permissions`
	CHANGE COLUMN `name` `name` VARCHAR(255) NULL DEFAULT NULL AFTER `id`,
	CHANGE COLUMN `label` `label` VARCHAR(255) NULL DEFAULT NULL AFTER `name`,
	CHANGE COLUMN `description` `description` VARCHAR(255) NULL DEFAULT NULL AFTER `label`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `description`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `profiles`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `timezone`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `projects`
  CHANGE COLUMN `title` `title` VARCHAR(255) NULL DEFAULT NULL AFTER `group_id`,
  CHANGE COLUMN `contact` `contact` VARCHAR(255) NULL DEFAULT NULL AFTER `slug`,
  CHANGE COLUMN `contact_email` `contact_email` VARCHAR(255) NULL DEFAULT NULL AFTER `contact`,
  CHANGE COLUMN `contact_title` `contact_title` VARCHAR(255) NULL DEFAULT NULL AFTER `contact_email`,
  CHANGE COLUMN `organization_website` `organization_website` VARCHAR(255) NULL DEFAULT NULL AFTER `contact_title`,
  CHANGE COLUMN `organization` `organization` VARCHAR(255) NULL DEFAULT NULL AFTER `organization_website`,
  CHANGE COLUMN `project_partners` `project_partners` TEXT NULL DEFAULT NULL AFTER `organization`,
  CHANGE COLUMN `funding_source` `funding_source` TEXT NULL DEFAULT NULL AFTER `project_partners`,
  CHANGE COLUMN `description_short` `description_short` VARCHAR(255) NULL DEFAULT NULL AFTER `funding_source`,
  CHANGE COLUMN `description_long` `description_long` TEXT NULL DEFAULT NULL AFTER `description_short`,
  CHANGE COLUMN `incentives` `incentives` TEXT NULL DEFAULT NULL AFTER `description_long`,
  CHANGE COLUMN `geographic_scope` `geographic_scope` VARCHAR(255) NULL DEFAULT NULL AFTER `incentives`,
  CHANGE COLUMN `taxonomic_scope` `taxonomic_scope` VARCHAR(255) NULL DEFAULT NULL AFTER `geographic_scope`,
  CHANGE COLUMN `temporal_scope` `temporal_scope` VARCHAR(255) NULL DEFAULT NULL AFTER `taxonomic_scope`,
  CHANGE COLUMN `keywords` `keywords` VARCHAR(255) NULL DEFAULT NULL AFTER `temporal_scope`,
  CHANGE COLUMN `blog_url` `blog_url` VARCHAR(255) NULL DEFAULT NULL AFTER `keywords`,
  CHANGE COLUMN `facebook` `facebook` VARCHAR(255) NULL DEFAULT NULL AFTER `blog_url`,
  CHANGE COLUMN `twitter` `twitter` VARCHAR(255) NULL DEFAULT NULL AFTER `facebook`,
  CHANGE COLUMN `activities` `activities` VARCHAR(255) NULL DEFAULT NULL AFTER `twitter`,
  CHANGE COLUMN `language_skills` `language_skills` VARCHAR(255) NULL DEFAULT NULL AFTER `activities`;
ALTER TABLE `projects`
  CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `fusion_template_id`,
  CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `properties`
	CHANGE COLUMN `namespace` `namespace` VARCHAR(255) NULL DEFAULT NULL AFTER `short`,
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `namespace`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `resources`
	CHANGE COLUMN `title` `title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `id`,
	CHANGE COLUMN `document` `document` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `description`;
ALTER TABLE `resources`
	CHANGE COLUMN `description` `description` TEXT NULL COLLATE 'utf8_unicode_ci' AFTER `title`;

ALTER TABLE `state_counties`
	CHANGE COLUMN `county_name` `county_name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `id`,
	CHANGE COLUMN `state_county` `state_county` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `county_name`,
	CHANGE COLUMN `state_abbr` `state_abbr` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `state_county`,
	CHANGE COLUMN `state_abbr_cap` `state_abbr_cap` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `state_abbr`,
	CHANGE COLUMN `geometry` `geometry` TEXT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `state_abbr_cap`,
	CHANGE COLUMN `value` `value` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `geometry`,
	CHANGE COLUMN `geo_id` `geo_id` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `value`,
	CHANGE COLUMN `geo_id_2` `geo_id_2` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `geo_id`,
	CHANGE COLUMN `geographic_name` `geographic_name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `geo_id_2`,
	CHANGE COLUMN `state_num` `state_num` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `geographic_name`,
	CHANGE COLUMN `county_num` `county_num` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `state_num`,
	CHANGE COLUMN `fips_forumla` `fips_forumla` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `county_num`,
	CHANGE COLUMN `has_error` `has_error` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `fips_forumla`;

ALTER TABLE `teams`
	CHANGE COLUMN `first_name` `first_name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `team_category_id`,
	CHANGE COLUMN `last_name` `last_name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `first_name`,
	CHANGE COLUMN `email` `email` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `last_name`,
	CHANGE COLUMN `institution` `institution` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `email`;

ALTER TABLE `users`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `activation_code`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `workflows`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `enabled`,
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;
