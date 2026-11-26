# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[semantic versioning](https://semver.org/).

## [1.1.0] - 2026-11-26

### Added
- "Shown to" per slide: everyone, visitors not logged in or logged-in customers, e.g. a slide inviting to open an
  account only for visitors. The preloaded first photo follows the same choice.
- Upgrade from 1.0.x adds the `audience` column; existing slides stay shown to everyone.

## [1.0.1] - 2026-11-19

### Changed
- The slider buttons are square with the radius of the optional custom property `--r-sm` (4 px when the theme does not set it).
- The slide steps are as tall as `--control-h-sm` (40 px when the theme does not set it).
- The slide progress bar is as thick as `--bar-h` (6 px when the theme does not set it), with the `--r-xs` radius (2 px).

## [1.0.0] - 2026-11-19

### Added
- Full-width slider on `displayHome` with photo and video slides, a label, title, text and up to two
  buttons per slide, texts per language.
- A video slide moves on when its video ends or after an optional limit in seconds; a photo slide
  after a set time (7 s by default). The progress bar of the current slide fills as it plays.
- Motion stops off-screen, on a hidden tab, with the pause button and with reduced motion; a single
  video slide still gets the pause button. A video loads only when its slide comes up, the next one
  is preloaded; videos and the photos of later slides wait 1.5 s after the page load, so only the
  first photo competes for the Largest Contentful Paint.
- The first frame of a video is captured in the browser when the video is chosen in the back office
  and stored as the slide photo, shown until the video plays; a photo chosen by hand wins.
- Photos stored in two widths and every format of `PS_IMAGE_FORMAT` under `img/m4p_heroslider/`;
  file types checked by content.
- The photo of the first slide is preloaded right after the page title (`displayAfterTitleTag`, phone
  and desktop widths, in the format of the first `<source>`), ahead of fonts and stylesheets.
- Slides per shop in a multistore; English and Polish catalogues.
