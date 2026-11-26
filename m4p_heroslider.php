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

require_once __DIR__ . '/classes/M4pHeroMedia.php';
require_once __DIR__ . '/classes/M4pHeroSlide.php';

class M4p_heroslider extends Module
{
    public const CONFIG_PHOTO_SECONDS = 'M4P_HEROSLIDER_PHOTO_SECONDS';
    private const DEFAULT_PHOTO_SECONDS = 7;
    private const LANG_FIELDS = ['kicker', 'title', 'text', 'cta_text', 'cta_url', 'cta2_text', 'cta2_url'];

    public function __construct()
    {
        $this->name = 'm4p_heroslider';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Modules4Presta';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Hero slider', [], 'Modules.M4pheroslider.Admin');
        $this->description = $this->trans('Full-width home page slider with video and photo slides that advance on their own.', [], 'Modules.M4pheroslider.Admin');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHook(['displayHome', 'displayAfterTitleTag', 'actionFrontControllerSetMedia', 'displayBackOfficeHeader'])
            && Configuration::updateValue(self::CONFIG_PHOTO_SECONDS, self::DEFAULT_PHOTO_SECONDS);
    }

    public function uninstall(): bool
    {
        foreach (M4pHeroSlide::getSlides((int) $this->context->shop->id, (int) Configuration::get('PS_LANG_DEFAULT'), false) as $row) {
            M4pHeroMedia::delete((string) $row['image']);
            M4pHeroMedia::delete((string) $row['video']);
        }
        Configuration::deleteByName(self::CONFIG_PHOTO_SECONDS);

        return $this->runSql('uninstall') && parent::uninstall();
    }

    private function runSql(string $step): bool
    {
        foreach ((array) (include __DIR__ . '/sql/' . $step . '.php') as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    // ---- Back office -------------------------------------------------------------------------

    public function getContent(): string
    {
        $output = $this->processActions();
        $slideId = (int) Tools::getValue('id_slide');
        if (Tools::isSubmit('addSlide') || $slideId > 0) {
            return $output . $this->renderSlideForm($slideId);
        }

        return $output . $this->renderSlideList() . $this->renderSettingsForm();
    }

    private function adminUrl(array $params = []): string
    {
        return $this->context->link->getAdminLink('AdminModules', true, [], array_merge(['configure' => $this->name], $params));
    }

    private function processActions(): string
    {
        $slideId = (int) Tools::getValue('id_slide');
        if (Tools::isSubmit('submitM4pHeroSettings')) {
            $seconds = (int) Tools::getValue(self::CONFIG_PHOTO_SECONDS);
            if ($seconds < 3 || $seconds > 30) {
                return $this->displayError($this->trans('Photo slide time: a number of seconds between 3 and 30.', [], 'Modules.M4pheroslider.Admin'));
            }
            Configuration::updateValue(self::CONFIG_PHOTO_SECONDS, $seconds);

            return $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.M4pheroslider.Admin'));
        }
        if (Tools::isSubmit('submitM4pHeroSlide')) {
            return $this->saveSlide($slideId);
        }
        $slide = $slideId > 0 ? new M4pHeroSlide($slideId) : null;
        if (!$slide || !Validate::isLoadedObject($slide) || (int) $slide->id_shop !== (int) $this->context->shop->id) {
            return '';
        }
        if (Tools::isSubmit('deleteSlide')) {
            $slide->delete();
            Tools::redirectAdmin($this->adminUrl(['conf' => 1]));
        }
        if (Tools::isSubmit('toggleSlide')) {
            $slide->active = !$slide->active;
            $slide->update();
            Tools::redirectAdmin($this->adminUrl(['conf' => 5]));
        }
        if (Tools::isSubmit('moveSlide')) {
            $slide->move((int) Tools::getValue('direction') < 0 ? -1 : 1);
            Tools::redirectAdmin($this->adminUrl(['conf' => 5]));
        }

        return '';
    }

    private function saveSlide(int $slideId): string
    {
        $slide = new M4pHeroSlide($slideId ?: null);
        if ($slideId && (int) $slide->id_shop !== (int) $this->context->shop->id) {
            return $this->displayError($this->trans('This slide belongs to another shop.', [], 'Modules.M4pheroslider.Admin'));
        }
        foreach (self::LANG_FIELDS as $field) {
            foreach (Language::getIDs(false) as $idLang) {
                $slide->{$field}[$idLang] = trim((string) Tools::getValue($field . '_' . $idLang));
            }
        }
        $slide->active = (bool) Tools::getValue('active');
        $slide->max_seconds = max(0, min(600, (int) Tools::getValue('max_seconds')));
        $audience = (string) Tools::getValue('audience');
        $slide->audience = in_array($audience, M4pHeroSlide::AUDIENCES, true) ? $audience : M4pHeroSlide::AUDIENCE_ALL;

        $errors = [];
        if (Tools::getValue('remove_video')) {
            M4pHeroMedia::delete((string) $slide->video);
            $slide->video = '';
        }
        $video = $this->uploadedFile('video');
        if ($video !== null) {
            $name = M4pHeroMedia::storeVideo($video, $this->uploadLimit());
            if ($name === '') {
                $errors[] = $this->trans('The video was not saved. Choose an MP4 or WebM file up to %size% MB.', ['%size%' => (int) ($this->uploadLimit() / 1048576)], 'Modules.M4pheroslider.Admin');
            } else {
                M4pHeroMedia::delete((string) $slide->video);
                $slide->video = $name;
            }
        }
        // The photo field wins; without it the first video frame captured in the browser becomes the poster.
        $image = $this->uploadedFile('image') ?? ($video !== null || ($slide->video && !$slide->image) ? $this->uploadedFile('poster') : null);
        if ($image !== null) {
            $name = M4pHeroMedia::storeImage($image);
            if ($name === '') {
                $errors[] = $this->trans('The photo was not saved. Choose a JPG, PNG or WEBP file up to 8 MB.', [], 'Modules.M4pheroslider.Admin');
            } else {
                M4pHeroMedia::delete((string) $slide->image);
                $slide->image = $name;
            }
        }
        if (!$slide->image && !$slide->video) {
            $errors[] = $this->trans('Add a photo or a video.', [], 'Modules.M4pheroslider.Admin');
        }
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        if (trim((string) ($slide->title[$defaultLang] ?? '')) === '') {
            $errors[] = $this->trans('Fill in the title.', [], 'Modules.M4pheroslider.Admin');
        }
        if ($errors !== []) {
            return $this->displayError(implode('<br>', $errors));
        }
        $slideId ? $slide->update() : $slide->add();
        Tools::redirectAdmin($this->adminUrl(['conf' => $slideId ? 4 : 3]));

        return '';
    }

    private function uploadedFile(string $field): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!$file || (int) $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            return null;
        }

        return (string) $file['tmp_name'];
    }

    /** The largest upload PHP accepts, in bytes. */
    private function uploadLimit(): int
    {
        $limits = array_map(static function (string $key): int {
            $value = trim((string) ini_get($key));
            $number = (int) $value;
            $unit = strtolower(substr($value, -1));

            return $number * (['g' => 1073741824, 'm' => 1048576, 'k' => 1024][$unit] ?? 1);
        }, ['upload_max_filesize', 'post_max_size']);

        return max(1, min(array_filter($limits) ?: [8388608]));
    }

    private function renderSlideList(): string
    {
        $idLang = (int) $this->context->language->id;
        $slides = [];
        foreach (M4pHeroSlide::getSlides((int) $this->context->shop->id, $idLang, false) as $row) {
            $id = (int) $row['id_m4p_heroslider_slide'];
            $image = M4pHeroMedia::imageSources((string) $row['image']);
            $slides[] = [
                'id' => $id,
                'title' => (string) $row['title'],
                'thumb' => $image['src_m'] ?? '',
                'video' => (string) $row['video'] !== '',
                'active' => (bool) $row['active'],
                'audience' => $this->audienceLabel((string) $row['audience']),
                'edit' => $this->adminUrl(['id_slide' => $id]),
                'toggle' => $this->adminUrl(['id_slide' => $id, 'toggleSlide' => 1]),
                'up' => $this->adminUrl(['id_slide' => $id, 'moveSlide' => 1, 'direction' => -1]),
                'down' => $this->adminUrl(['id_slide' => $id, 'moveSlide' => 1, 'direction' => 1]),
                'delete' => $this->adminUrl(['id_slide' => $id, 'deleteSlide' => 1]),
            ];
        }
        $this->context->smarty->assign([
            'm4pHeroSlides' => $slides,
            'm4pHeroAdd' => $this->adminUrl(['addSlide' => 1]),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/slides.tpl');
    }

    private function renderSettingsForm(): string
    {
        $helper = $this->formHelper('submitM4pHeroSettings');
        $helper->fields_value = [self::CONFIG_PHOTO_SECONDS => (int) Configuration::get(self::CONFIG_PHOTO_SECONDS) ?: self::DEFAULT_PHOTO_SECONDS];

        return $helper->generateForm([[
            'form' => [
                'legend' => ['title' => $this->trans('Settings', [], 'Modules.M4pheroslider.Admin'), 'icon' => 'icon-cogs'],
                'input' => [[
                    'type' => 'text', 'name' => self::CONFIG_PHOTO_SECONDS, 'class' => 'fixed-width-sm',
                    'suffix' => $this->trans('s', [], 'Modules.M4pheroslider.Admin'),
                    'label' => $this->trans('Photo slide time', [], 'Modules.M4pheroslider.Admin'),
                    'desc' => $this->trans('How long a photo slide stays before the next one. A video slide moves on when the video ends.', [], 'Modules.M4pheroslider.Admin'),
                ]],
                'submit' => ['title' => $this->trans('Save', [], 'Modules.M4pheroslider.Admin')],
            ],
        ]]);
    }

    private function renderSlideForm(int $slideId): string
    {
        $slide = new M4pHeroSlide($slideId ?: null);
        if ($slideId && (int) $slide->id_shop !== (int) $this->context->shop->id) {
            Tools::redirectAdmin($this->adminUrl());
        }
        $helper = $this->formHelper('submitM4pHeroSlide');
        $helper->currentIndex .= $slideId ? '&id_slide=' . $slideId : '&addSlide=1';
        $helper->languages = $this->context->controller->getLanguages();
        $helper->fields_value = [
            'active' => $slideId ? (int) $slide->active : 1,
            'audience' => Tools::getValue('audience', $slide->audience ?: M4pHeroSlide::AUDIENCE_ALL),
            'max_seconds' => (int) $slide->max_seconds ?: '',
            'remove_video' => 0,
        ];
        foreach (self::LANG_FIELDS as $field) {
            foreach (Language::getIDs(false) as $idLang) {
                $helper->fields_value[$field][$idLang] = Tools::getValue($field . '_' . $idLang, $slide->{$field}[$idLang] ?? '');
            }
        }
        $image = M4pHeroMedia::imageSources((string) $slide->image);
        $preview = $image ? '<img src="' . htmlspecialchars($image['src_m'], ENT_QUOTES) . '" alt="" class="m4p-hero-admin__preview">' : '';
        $text = function (string $name, string $label, string $desc = ''): array {
            return ['type' => 'text', 'name' => $name, 'label' => $label, 'lang' => true, 'desc' => $desc];
        };
        $switch = function (string $name, string $label, string $desc = ''): array {
            return [
                'type' => 'switch', 'name' => $name, 'label' => $label, 'desc' => $desc, 'is_bool' => true,
                'values' => [
                    ['id' => $name . '_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                    ['id' => $name . '_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                ],
            ];
        };
        $inputs = [
            $switch('active', $this->trans('Shown', [], 'Modules.M4pheroslider.Admin')),
            [
                'type' => 'select', 'name' => 'audience', 'label' => $this->trans('Shown to', [], 'Modules.M4pheroslider.Admin'),
                'options' => ['query' => array_map(function (string $audience): array {
                    return ['id' => $audience, 'name' => $this->audienceLabel($audience)];
                }, M4pHeroSlide::AUDIENCES), 'id' => 'id', 'name' => 'name'],
                'desc' => $this->trans('E.g. a slide inviting to open an account only for visitors who are not logged in.', [], 'Modules.M4pheroslider.Admin'),
            ],
            $text('kicker', $this->trans('Label above the title', [], 'Modules.M4pheroslider.Admin'), $this->trans('One or two words, e.g. "Season".', [], 'Modules.M4pheroslider.Admin')),
            $text('title', $this->trans('Title', [], 'Modules.M4pheroslider.Admin')),
            ['type' => 'textarea', 'name' => 'text', 'label' => $this->trans('Text', [], 'Modules.M4pheroslider.Admin'), 'lang' => true, 'rows' => 3],
            $text('cta_text', $this->trans('Main button text', [], 'Modules.M4pheroslider.Admin')),
            $text('cta_url', $this->trans('Main button address', [], 'Modules.M4pheroslider.Admin')),
            $text('cta2_text', $this->trans('Second button text', [], 'Modules.M4pheroslider.Admin'), $this->trans('Optional, an outlined button next to the main one.', [], 'Modules.M4pheroslider.Admin')),
            $text('cta2_url', $this->trans('Second button address', [], 'Modules.M4pheroslider.Admin')),
            [
                'type' => 'file', 'name' => 'video', 'label' => $this->trans('Video', [], 'Modules.M4pheroslider.Admin'),
                'desc' => $this->trans('MP4 or WebM up to %size% MB, landscape 16:9, without sound; a few seconds is enough. The first frame becomes the photo shown until the video loads.', ['%size%' => (int) ($this->uploadLimit() / 1048576)], 'Modules.M4pheroslider.Admin')
                    . ($slide->video ? ' ' . $this->trans('Current video: %file%.', ['%file%' => $slide->video], 'Modules.M4pheroslider.Admin') : ''),
            ],
        ];
        if ($slide->video) {
            $inputs[] = $switch('remove_video', $this->trans('Remove the video', [], 'Modules.M4pheroslider.Admin'), $this->trans('The slide keeps its photo.', [], 'Modules.M4pheroslider.Admin'));
        }
        $inputs[] = [
            'type' => 'file', 'name' => 'image', 'label' => $this->trans('Photo', [], 'Modules.M4pheroslider.Admin'),
            'desc' => $this->trans('JPG, PNG or WEBP up to 8 MB, landscape 16:9 or wider. Keep the subject on the right – the text sits on the left. With a video, a photo chosen here replaces the first frame.', [], 'Modules.M4pheroslider.Admin'),
            'image' => $preview ?: false,
        ];
        $inputs[] = [
            'type' => 'text', 'name' => 'max_seconds', 'class' => 'fixed-width-sm', 'suffix' => $this->trans('s', [], 'Modules.M4pheroslider.Admin'),
            'label' => $this->trans('Longest video time', [], 'Modules.M4pheroslider.Admin'),
            'desc' => $this->trans('Empty: the slide moves on when the video ends. A number: after that many seconds at most.', [], 'Modules.M4pheroslider.Admin'),
        ];

        return $helper->generateForm([[
            'form' => [
                'legend' => ['title' => $slideId ? $this->trans('Edit slide', [], 'Modules.M4pheroslider.Admin') : $this->trans('New slide', [], 'Modules.M4pheroslider.Admin'), 'icon' => 'icon-picture'],
                'input' => $inputs,
                'submit' => ['title' => $this->trans('Save', [], 'Modules.M4pheroslider.Admin')],
                'buttons' => [['href' => $this->adminUrl(), 'title' => $this->trans('Back to the list', [], 'Modules.M4pheroslider.Admin'), 'icon' => 'process-icon-back']],
            ],
        ]]);
    }

    private function audienceLabel(string $audience): string
    {
        switch ($audience) {
            case M4pHeroSlide::AUDIENCE_GUESTS:
                return $this->trans('Visitors not logged in', [], 'Modules.M4pheroslider.Admin');
            case M4pHeroSlide::AUDIENCE_CUSTOMERS:
                return $this->trans('Logged-in customers', [], 'Modules.M4pheroslider.Admin');
            default:
                return $this->trans('Everyone', [], 'Modules.M4pheroslider.Admin');
        }
    }

    private function formHelper(string $submit): HelperForm
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = $submit;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');

        return $helper;
    }

    public function hookDisplayBackOfficeHeader(): void
    {
        if (Tools::getValue('configure') !== $this->name) {
            return;
        }
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
    }

    // ---- Front office ------------------------------------------------------------------------

    public function hookActionFrontControllerSetMedia(): void
    {
        if (($this->context->controller->php_self ?? '') !== 'index') {
            return;
        }
        $base = 'modules/' . $this->name . '/views/';
        $this->context->controller->registerStylesheet('m4p-heroslider', $base . 'css/front.css', [
            'media' => 'all', 'priority' => 150, 'version' => (string) filemtime(__DIR__ . '/views/css/front.css'),
        ]);
        // In the head with defer: the theme may delay bottom scripts until the first interaction.
        $this->context->controller->registerJavascript('m4p-heroslider', $base . 'js/front.js', [
            'position' => 'head', 'priority' => 150, 'attributes' => 'defer', 'version' => (string) filemtime(__DIR__ . '/views/js/front.js'),
        ]);
    }

    /**
     * Preloads the photo of the first slide, the Largest Contentful Paint of the home page. Printed right
     * after the title, ahead of the font and stylesheet requests: on HTTP/1.1 their six connections would
     * otherwise be taken before the photo is asked for.
     */
    public function hookDisplayAfterTitleTag(): string
    {
        if (($this->context->controller->php_self ?? '') !== 'index') {
            return '';
        }
        $first = $this->frontSlides()[0] ?? null;
        $image = $first ? M4pHeroMedia::imageSources((string) $first['image']) : [];
        if ($image === []) {
            return '';
        }
        // The first <source> of the template wins in the browser, so its format is the one to preload.
        $format = (string) array_key_first($image['formats']);
        [$urls, $type] = $format !== ''
            ? [$image['formats'][$format], ' type="image/' . htmlspecialchars($format, ENT_QUOTES) . '"']
            : [[$image['src'], $image['src_m']], ''];

        return '<link rel="preload" as="image" fetchpriority="high"' . $type . ' href="' . htmlspecialchars($urls[1], ENT_QUOTES) . '" media="(max-width: 719px)">' . "\n"
            . '<link rel="preload" as="image" fetchpriority="high"' . $type . ' href="' . htmlspecialchars($urls[0], ENT_QUOTES) . '" media="(min-width: 720px)">' . "\n";
    }

    public function hookDisplayHome(): string
    {
        $slides = [];
        foreach ($this->frontSlides() as $row) {
            if (trim((string) $row['title']) === '') {
                continue;
            }
            $slides[] = [
                'kicker' => (string) $row['kicker'],
                'title' => (string) $row['title'],
                'text' => (string) $row['text'],
                'cta_text' => (string) $row['cta_text'],
                'cta_url' => (string) $row['cta_url'],
                'cta2_text' => (string) $row['cta2_text'],
                'cta2_url' => (string) $row['cta2_url'],
                'image' => M4pHeroMedia::imageSources((string) $row['image']),
                'video' => (string) $row['video'] !== '' ? M4pHeroMedia::url((string) $row['video']) : '',
                'max_seconds' => (int) $row['max_seconds'],
            ];
        }
        if ($slides === []) {
            return '';
        }
        $this->context->smarty->assign([
            'm4pHeroSlides' => $slides,
            'm4pHeroPhotoMs' => 1000 * ((int) Configuration::get(self::CONFIG_PHOTO_SECONDS) ?: self::DEFAULT_PHOTO_SECONDS),
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/hero.tpl');
    }

    /**
     * Active slides for the current visitor: those for everyone and those for guests or for logged-in customers.
     *
     * @return array<int, array<string, mixed>>
     */
    private function frontSlides(): array
    {
        $audience = $this->context->customer->isLogged() ? M4pHeroSlide::AUDIENCE_CUSTOMERS : M4pHeroSlide::AUDIENCE_GUESTS;

        return M4pHeroSlide::getSlides((int) $this->context->shop->id, (int) $this->context->language->id, true, $audience);
    }
}
