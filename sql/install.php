<?php
/**
 * m4p_heroslider
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

return [
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'm4p_heroslider_slide` (
        `id_m4p_heroslider_slide` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_shop` INT UNSIGNED NOT NULL,
        `position` INT UNSIGNED NOT NULL DEFAULT 0,
        `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
        `image` VARCHAR(64) NOT NULL DEFAULT \'\',
        `video` VARCHAR(64) NOT NULL DEFAULT \'\',
        `max_seconds` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        `audience` VARCHAR(16) NOT NULL DEFAULT \'all\',
        `date_add` DATETIME NOT NULL,
        `date_upd` DATETIME NOT NULL,
        PRIMARY KEY (`id_m4p_heroslider_slide`),
        KEY `shop_position` (`id_shop`, `position`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;',
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'm4p_heroslider_slide_lang` (
        `id_m4p_heroslider_slide` INT UNSIGNED NOT NULL,
        `id_lang` INT UNSIGNED NOT NULL,
        `kicker` VARCHAR(64) NOT NULL DEFAULT \'\',
        `title` VARCHAR(160) NOT NULL DEFAULT \'\',
        `text` VARCHAR(400) NOT NULL DEFAULT \'\',
        `cta_text` VARCHAR(64) NOT NULL DEFAULT \'\',
        `cta_url` VARCHAR(512) NOT NULL DEFAULT \'\',
        `cta2_text` VARCHAR(64) NOT NULL DEFAULT \'\',
        `cta2_url` VARCHAR(512) NOT NULL DEFAULT \'\',
        PRIMARY KEY (`id_m4p_heroslider_slide`, `id_lang`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;',
];
