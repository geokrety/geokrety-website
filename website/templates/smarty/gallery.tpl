{include file='macros/converters.tpl'}
{include file='macros/picture.tpl'}
{include file='macros/icons.tpl'}
{include file='macros/paginate.tpl'}

<ol class="breadcrumb">
  <li><a href="/">{t}Home{/t}</a></li>
  <li class="active">{t}Gallery{/t}</li>
</ol>

<a class="anchor" id="gallery"></a>
{call pagination total=$totalPictures perpage=$picturesPerGalleryPage anchor='gallery'}
<div class="gallery">
  {foreach from=$pictures item=item}
  {call picture item=$item}
  {foreachelse}
  <em>{t}No picture uploaded yet.{/t}</em>
  {/foreach}
</div>
{call pagination total=$totalPictures perpage=$picturesPerGalleryPage anchor='gallery'}
