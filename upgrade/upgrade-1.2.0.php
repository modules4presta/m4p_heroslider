<?php
/**
 * m4p_heroslider
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/** Adds the shop view of a slide; existing slides stay shown in both views. */
function upgrade_module_1_2_0(M4p_Heroslider $module): bool
{
    $table = _DB_PREFIX_ . 'm4p_heroslider_slide';
    $exists = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . $table . '` LIKE \'view\'');
    if ($exists) {
        return true;
    }

    return Db::getInstance()->execute('ALTER TABLE `' . $table . '` ADD `view` VARCHAR(8) NOT NULL DEFAULT \'both\' AFTER `audience`');
}
