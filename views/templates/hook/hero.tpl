{**
 * m4p_heroslider
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{$total = $m4pHeroSlides|@count}
<section class="m4p-hero" aria-roledescription="{l s='carousel' d='Modules.M4pheroslider.Shop'}" aria-label="{l s='Featured offers' d='Modules.M4pheroslider.Shop'}"
  data-m4p-hero data-photo-ms="{$m4pHeroPhotoMs|intval}"
  data-label-pause="{l s='Pause the slider' d='Modules.M4pheroslider.Shop'}" data-label-play="{l s='Play the slider' d='Modules.M4pheroslider.Shop'}">
  {foreach from=$m4pHeroSlides item=slide name=slides}
    {$first = $smarty.foreach.slides.first}
    <div class="m4p-hero__slide{if $first} is-active{/if}" role="group" aria-roledescription="{l s='slide' d='Modules.M4pheroslider.Shop'}"
      aria-label="{l s='%number% of %total%' sprintf=['%number%' => $smarty.foreach.slides.iteration, '%total%' => $total] d='Modules.M4pheroslider.Shop'}"{if !$first} inert{/if}>
      <div class="m4p-hero__media">
        {if $slide.image}
          {* Photos of the later slides wait in data-* until the page has loaded, off the LCP path. *}
          {$src = ($first) ? 'src' : 'data-src'}
          {$srcset = ($first) ? 'srcset' : 'data-srcset'}
          <picture class="m4p-hero__picture">
            {foreach from=$slide.image.formats key=format item=urls}
              <source type="image/{$format|escape:'html':'UTF-8'}" media="(max-width: 719px)" {$srcset}="{$urls[1]|escape:'html':'UTF-8'}">
              <source type="image/{$format|escape:'html':'UTF-8'}" {$srcset}="{$urls[0]|escape:'html':'UTF-8'}">
            {/foreach}
            <source media="(max-width: 719px)" {$srcset}="{$slide.image.src_m|escape:'html':'UTF-8'}">
            <img {$src}="{$slide.image.src|escape:'html':'UTF-8'}" alt="" width="2048" height="1152" decoding="async"{if $first} fetchpriority="high"{/if}>
          </picture>
        {/if}
        {if $slide.video}
          {* The photo underneath holds the first frame until the video plays. *}
          <video class="m4p-hero__video" muted playsinline preload="none" data-src="{$slide.video|escape:'html':'UTF-8'}"
            {if $slide.max_seconds}data-max-s="{$slide.max_seconds|intval}"{/if}></video>
        {/if}
      </div>
      <div class="m4p-hero__caption">
        <div class="m4p-hero__text">
          {if $slide.kicker}<p class="m4p-hero__kicker">{$slide.kicker|escape:'html':'UTF-8'}</p>{/if}
          {if $first}
            <h2 class="m4p-hero__title">{$slide.title|escape:'html':'UTF-8'}</h2>
          {else}
            <p class="m4p-hero__title">{$slide.title|escape:'html':'UTF-8'}</p>
          {/if}
          {if $slide.text}<p class="m4p-hero__lead">{$slide.text|escape:'html':'UTF-8'}</p>{/if}
          {if $slide.cta_text || $slide.cta2_text}
            <div class="m4p-hero__actions">
              {if $slide.cta_text}<a class="m4p-hero__cta btn btn-lg" href="{$slide.cta_url|escape:'html':'UTF-8'}">{$slide.cta_text|escape:'html':'UTF-8'}</a>{/if}
              {if $slide.cta2_text}<a class="m4p-hero__cta m4p-hero__cta--secondary btn btn-lg" href="{$slide.cta2_url|escape:'html':'UTF-8'}">{$slide.cta2_text|escape:'html':'UTF-8'}</a>{/if}
            </div>
          {/if}
        </div>
      </div>
    </div>
  {/foreach}

  {* A single video slide still gets the pause button (WCAG 2.2.2), without the slide controls. *}
  {if $total > 1 || $m4pHeroSlides[0].video}
    <div class="m4p-hero__controls">
      <div class="m4p-hero__controls-row">
        {if $total > 1}
        <span class="m4p-hero__count" aria-hidden="true">01 / {$total|string_format:'%02d'}</span>
        <div class="m4p-hero__progress">
          {foreach from=$m4pHeroSlides item=slide name=dots}
            <button type="button" class="m4p-hero__step" data-go="{$smarty.foreach.dots.index|intval}"
              aria-label="{l s='Slide %number%: %title%' sprintf=['%number%' => $smarty.foreach.dots.iteration, '%title%' => $slide.title] d='Modules.M4pheroslider.Shop'}"
              {if $smarty.foreach.dots.first}aria-current="true"{/if}><span><i></i></span></button>
          {/foreach}
        </div>
        {/if}
        <div class="m4p-hero__buttons">
          {if $total > 1}
          <button type="button" class="m4p-hero__button" data-step="-1" aria-label="{l s='Previous slide' d='Modules.M4pheroslider.Shop'}">
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M19 12H5m7 7-7-7 7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <button type="button" class="m4p-hero__button" data-step="1" aria-label="{l s='Next slide' d='Modules.M4pheroslider.Shop'}">
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M5 12h14m-7-7 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          {/if}
          <button type="button" class="m4p-hero__button" data-pause aria-label="{l s='Pause the slider' d='Modules.M4pheroslider.Shop'}">
            <svg class="m4p-hero__icon-pause" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><rect x="6" y="5" width="4" height="14" rx="1" fill="currentColor"/><rect x="14" y="5" width="4" height="14" rx="1" fill="currentColor"/></svg>
            <svg class="m4p-hero__icon-play" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.4-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z" fill="currentColor"/></svg>
          </button>
        </div>
      </div>
    </div>
  {/if}
</section>
