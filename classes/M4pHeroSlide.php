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

/**
 * One slide of one shop: a photo, or a video with its poster (the photo field holds the poster).
 */
class M4pHeroSlide extends ObjectModel
{
    public const TABLE = 'm4p_heroslider_slide';
    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_GUESTS = 'guests';
    public const AUDIENCE_CUSTOMERS = 'customers';
    public const AUDIENCES = [self::AUDIENCE_ALL, self::AUDIENCE_GUESTS, self::AUDIENCE_CUSTOMERS];

    public $id_shop;
    public $position = 0;
    public $active = true;
    public $image = '';
    public $video = '';
    public $max_seconds = 0;
    public $audience = self::AUDIENCE_ALL;
    public $date_add;
    public $date_upd;

    public $kicker;
    public $title;
    public $text;
    public $cta_text;
    public $cta_url;
    public $cta2_text;
    public $cta2_url;

    public static $definition = [
        'table' => self::TABLE,
        'primary' => 'id_m4p_heroslider_slide',
        'multilang' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isFileName', 'size' => 64],
            'video' => ['type' => self::TYPE_STRING, 'validate' => 'isFileName', 'size' => 64],
            'max_seconds' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'audience' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 16],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'kicker' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 64],
            'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 160],
            'text' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 400],
            'cta_text' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 64],
            'cta_url' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrlOrEmpty', 'size' => 512],
            'cta2_text' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 64],
            'cta2_url' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrlOrEmpty', 'size' => 512],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->id_shop) {
            $this->id_shop = (int) Context::getContext()->shop->id;
        }
        if (!$this->position) {
            $this->position = 1 + (int) Db::getInstance()->getValue('SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . self::TABLE
                . '` WHERE `id_shop` = ' . (int) $this->id_shop);
        }

        return parent::add($autoDate, $nullValues);
    }

    public function delete()
    {
        M4pHeroMedia::delete($this->image);
        M4pHeroMedia::delete($this->video);

        return parent::delete();
    }

    /**
     * Slides of a shop in their order, with the texts of one language.
     *
     * @param string|null $audience guests or customers: only the slides for everyone and for that audience
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSlides(int $idShop, int $idLang, bool $activeOnly, ?string $audience = null): array
    {
        $forAudience = $audience === null ? '' : ' AND s.`audience` IN (\'' . self::AUDIENCE_ALL . '\', \'' . pSQL($audience) . '\')';

        return Db::getInstance()->executeS('SELECT s.*, sl.*
            FROM `' . _DB_PREFIX_ . self::TABLE . '` s
            LEFT JOIN `' . _DB_PREFIX_ . self::TABLE . '_lang` sl
                ON sl.`id_m4p_heroslider_slide` = s.`id_m4p_heroslider_slide` AND sl.`id_lang` = ' . $idLang . '
            WHERE s.`id_shop` = ' . $idShop . ($activeOnly ? ' AND s.`active` = 1' : '') . $forAudience . '
            ORDER BY s.`position` ASC, s.`id_m4p_heroslider_slide` ASC') ?: [];
    }

    /** Moves the slide one place up (-1) or down (1) within its shop, renumbering the shop's slides. */
    public function move(int $direction): bool
    {
        $ids = array_map(
            static function (array $row): int { return (int) $row['id_m4p_heroslider_slide']; },
            self::getSlides((int) $this->id_shop, (int) Configuration::get('PS_LANG_DEFAULT'), false)
        );
        $index = array_search((int) $this->id, $ids, true);
        $target = $index === false ? false : $index + $direction;
        if ($target === false || $target < 0 || $target >= count($ids)) {
            return false;
        }
        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
        foreach ($ids as $position => $id) {
            Db::getInstance()->update(self::TABLE, ['position' => $position + 1], '`id_m4p_heroslider_slide` = ' . $id);
        }

        return true;
    }
}
