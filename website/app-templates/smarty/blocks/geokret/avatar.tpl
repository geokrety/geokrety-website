{include file='macros/picture_geokret_avatar.tpl'}
<div class="panel panel-default">
  <div id="geokretAvatar" class="panel-body {if $geokret->isOwner()}dropzone dz-clickable{/if}">
      {call geokret_avatar_default writable=$geokret->isOwner()}
  </div>
</div>
