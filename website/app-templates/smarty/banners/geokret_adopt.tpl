{if is_null($geokret->owner)}
<div class="alert alert-info alert-dismissible" role="alert">
  {if $f3->get('SESSION.CURRENT_USER')}
  {t escape=no page={'geokret_claim'|alias}}This GeoKret is available for adoption. You can <a href="%1">claim</a> this GeoKret.{/t}
  {else}
  {t escape=no page={'login'|alias}}This GeoKret is available for adoption. Please <a href="%1">login</a> first.{/t}
  {/if}
</div>
{/if}
