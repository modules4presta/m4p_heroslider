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
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'm4p_heroslider_slide_lang`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'm4p_heroslider_slide`',
];
