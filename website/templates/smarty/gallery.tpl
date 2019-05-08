{include file='macros/converters.tpl'}
{include file='macros/picture.tpl'}
{include file='macros/icons.tpl'}
{include file='macros/links_gk.tpl'}
{include file='macros/links_user.tpl'}

{function pagination perpage=$picturesPerGalleryPage}
{if $total > $perpage}
<div class="pull-right">
  {paginate total=$total fragment='gallery' pagesAroundActive=4 pagesBeforeSeparator=4 perPage=$perpage}
</div>
<div class="clearfix"></div>
{/if}
{/function}

<ol class="breadcrumb">
  <li><a href="/">{t}Home{/t}</a></li>
  <li class="active">{t}Gallery{/t}</li>
</ol>

<a class="anchor" id="gallery"></a>
{pagination total=$totalPictures}
<div class="gallery">
  {foreach from=$pictures item=item}
  {call picture item=$item}
  {foreachelse}
  <em>{t}No picture uploaded yet.{/t}</em>
  {/foreach}
</div>
{pagination total=$totalPictures}
