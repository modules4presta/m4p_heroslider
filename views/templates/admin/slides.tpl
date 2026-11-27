{**
 * m4p_heroslider
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<div class="panel m4p-hero-admin">
  <div class="panel-heading">
    <i class="icon-picture"></i> {l s='Slides' d='Modules.M4pheroslider.Admin'}
    <span class="panel-heading-action">
      <a class="list-toolbar-btn" href="{$m4pHeroAdd|escape:'html':'UTF-8'}" title="{l s='Add a slide' d='Modules.M4pheroslider.Admin'}"><i class="process-icon-new"></i></a>
    </span>
  </div>
  {if $m4pHeroSlides}
    <table class="table">
      <thead>
        <tr>
          <th>{l s='Position' d='Modules.M4pheroslider.Admin'}</th>
          <th></th>
          <th>{l s='Title' d='Modules.M4pheroslider.Admin'}</th>
          <th>{l s='Type' d='Modules.M4pheroslider.Admin'}</th>
          <th>{l s='Shown to' d='Modules.M4pheroslider.Admin'}</th>
          <th class="text-center">{l s='Shown' d='Modules.M4pheroslider.Admin'}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$m4pHeroSlides item=slide name=slides}
          <tr>
            <td class="m4p-hero-admin__order">
              {$smarty.foreach.slides.iteration|intval}
              {if !$smarty.foreach.slides.first}<a href="{$slide.up|escape:'html':'UTF-8'}" title="{l s='Move up' d='Modules.M4pheroslider.Admin'}"><i class="icon-arrow-up"></i></a>{/if}
              {if !$smarty.foreach.slides.last}<a href="{$slide.down|escape:'html':'UTF-8'}" title="{l s='Move down' d='Modules.M4pheroslider.Admin'}"><i class="icon-arrow-down"></i></a>{/if}
            </td>
            <td>{if $slide.thumb}<img src="{$slide.thumb|escape:'html':'UTF-8'}" alt="" class="m4p-hero-admin__thumb">{/if}</td>
            <td><a href="{$slide.edit|escape:'html':'UTF-8'}">{$slide.title|escape:'html':'UTF-8'}</a></td>
            <td>{if $slide.video}{l s='Video' d='Modules.M4pheroslider.Admin'}{else}{l s='Photo' d='Modules.M4pheroslider.Admin'}{/if}</td>
            <td>{$slide.audience|escape:'html':'UTF-8'}<br><small>{$slide.view|escape:'html':'UTF-8'}</small></td>
            <td class="text-center">
              <a href="{$slide.toggle|escape:'html':'UTF-8'}" class="list-action-enable {if $slide.active}action-enabled{else}action-disabled{/if}">
                <i class="{if $slide.active}icon-check{else}icon-remove{/if}"></i>
              </a>
            </td>
            <td class="text-right">
              <a class="btn btn-default" href="{$slide.edit|escape:'html':'UTF-8'}"><i class="icon-pencil"></i> {l s='Edit' d='Modules.M4pheroslider.Admin'}</a>
              <a class="btn btn-default" href="{$slide.delete|escape:'html':'UTF-8'}" data-m4p-hero-confirm="{l s='Delete this slide with its files?' d='Modules.M4pheroslider.Admin'}"><i class="icon-trash"></i> {l s='Delete' d='Modules.M4pheroslider.Admin'}</a>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {else}
    <p>{l s='No slides yet. Add the first one – the slider shows on the home page once it has a slide.' d='Modules.M4pheroslider.Admin'}</p>
    <a class="btn btn-primary" href="{$m4pHeroAdd|escape:'html':'UTF-8'}">{l s='Add a slide' d='Modules.M4pheroslider.Admin'}</a>
  {/if}
</div>
