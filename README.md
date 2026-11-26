# Hero Slider for PrestaShop 8 & 9

**A full-width home page slider that mixes short videos and photos, shows each offer for exactly as long as it takes and keeps the first photo fast.**

> **Meta description (155 chars):** Hero slider for PrestaShop with video and photo slides, progress bar, pause button and a fast first photo. Multistore. Free, MIT.

---

## Why the hero section matters for your store

The top of the home page is the first thing every visitor sees, and it is also the largest element
the browser has to paint. A heavy slider with several videos loading at once delays that paint,
moves the layout and pulls the visitor's attention around with motion they cannot stop.

A hero that loads one photo first, plays a video only when its slide comes up and lets the visitor
pause keeps the page fast and the message readable.

- **Video where it sells, photo where it is enough** – each slide is either a short muted video or a photo
- **Fast first paint** – only the first photo competes for the Largest Contentful Paint
- **Motion under control** – a pause button, no motion with reduced motion, nothing plays off-screen

## What the module does

The module adds a slider to `displayHome` and a slide manager to its configuration page. Slides,
their texts per language and their files belong to the shop they were added in. The module ships
its own templates, a base stylesheet and a script; colours and fonts come from the theme.

### Key features

- **Video and photo slides** – a label, a title, a text and up to two buttons per slide, in every language
- **Audience per slide** – everyone, visitors not logged in or logged-in customers; the preloaded first photo is the first slide of that audience
- **Timing** – a video slide moves on when its video ends or after an optional limit in seconds; a photo slide after the photo slide time (7 s by default); the progress bar of the current slide fills as it plays
- **First frame, automatically** – when a video is chosen in the back office, the browser captures its first frame and stores it as the slide photo, shown until the video plays; a photo chosen by hand replaces it, and no server tools are needed
- **Light on the page** – a video loads only when its slide comes up and the next one is preloaded; videos and the photos of later slides wait 1.5 s after the page load
- **Preloaded first photo** – the photo of the first slide is preloaded right after the page title (`displayAfterTitleTag`), in phone and desktop widths
- **Images in the shop's formats** – photos are stored in two widths and in every format set in `PS_IMAGE_FORMAT`, under `img/m4p_heroslider/`; file types are checked by content
- **Accessible** – carousel and slide roles, labelled controls and a pause button, also for a single video slide (WCAG 2.2.2)
- **Multistore** – every shop keeps its own slides

### Reduced motion, hidden tabs, uninstalling

With reduced motion the slider does not move on by itself and no video plays until the visitor
presses play. Off-screen and on a hidden tab, motion stops. Uninstalling the module drops its two
tables, deletes the slide files and its setting.

## Compatibility

| | |
|---|---|
| PrestaShop | 8.0 – 9.x |
| PHP | 7.4+, `fileinfo` and GD |
| Requirements | none |
| Multistore | Compatible – slides per shop |
| Themes | Works with any theme – templates and a base layout ship in the module; style the `.m4p-hero__*` classes to match the theme |

The module performs no core overrides. It adds two database tables
(`m4p_heroslider_slide`, `m4p_heroslider_slide_lang`) and one setting.

## Installation

1. Upload and install the module from **Modules → Module Manager**.
2. Open **Configure** and add the first slide.
3. In **Design → Positions → displayHome** place the module at the top of the home page.

Upload limits come from PHP (`upload_max_filesize`, `post_max_size`): a video larger than the limit
is rejected. Keep hero videos short and compressed.

## Configuration options

| Setting | Description |
|---|---|
| **Slides** | Add, edit, show or hide, reorder and delete slides. |
| **Photo slide time** | Seconds a photo slide stays, 3–30. 7 s by default. |

Each slide has: shown, shown to (everyone, visitors not logged in or logged-in customers), label
above the title, title (required), text, main and second button (text and address), video (MP4 or
WebM, landscape 16:9, without sound), photo (JPG, PNG or WEBP, landscape 16:9 or wider, subject on
the right) and the longest video time.

## Frequently asked questions

**Does a video slide need a photo?**
No. The browser captures the first frame of the video when you choose it and stores it as the photo.
Upload a photo only when you want a different picture before the video plays.

**Does the slider slow down the home page?**
Only the first photo loads with the page. Videos and the photos of later slides wait until after the
page load, and a video loads only when its slide comes up.

**Can visitors stop the motion?**
Yes. The slider has a pause button whenever something moves, and with reduced motion set in the system nothing moves on its own.

**Can I undo the installation?**
Yes. Uninstalling drops the module tables and deletes its files from `img/m4p_heroslider/`.

---

**Keywords:** PrestaShop hero slider, PrestaShop video slider, home page banner, video background, carousel, Largest Contentful Paint

## License

MIT – see [LICENSE](LICENSE). Free to use commercially, fork and modify; keep the copyright notice.

## Contributing

Bug reports and pull requests are welcome – see [CONTRIBUTING.md](CONTRIBUTING.md). For security
issues, follow [SECURITY.md](SECURITY.md) instead of opening a public issue.

---

Built by [Nice Code](https://nice-code.com/pl/produkty/sklep-b2b-prestashop) – we build and maintain PrestaShop stores.

© Nice Code sp. z o.o. (Modules4Presta) – released under the MIT license.
