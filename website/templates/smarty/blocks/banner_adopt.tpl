{if !$geokret_details->ownerId}
<div class="alert alert-info alert-dismissible" role="alert">
  {if $isLoggedIn}
  {t escape=no page="claim.php"}This GeoKret is available for adoption. You can <a href="%1">claim</a> this GeoKret.{/t}
  {else}
  {t escape=no page="longin.php"}This GeoKret is available for adoption. Please <a href="%1">login</a> first.{/t}
  {/if}
</div>
{/if}
