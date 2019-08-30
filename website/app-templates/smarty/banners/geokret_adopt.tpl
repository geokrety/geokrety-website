{if $geokret->owner_codes}
<div class="alert alert-info alert-dismissible" role="alert">
  {if $geokret->isOwner()}
  {t escape=no token=$geokret->owner_codes.0->token tracking_code=$geokret->tracking_code}You have set this GeoKret available for adoption. The Owner code is: <strong>%1</strong>. Give it along with the Tracking code <strong>%2</strong> to user which will adopt your GeoKret.{/t}
  {else if $f3->get('SESSION.CURRENT_USER')}
  {t escape=no page={'geokret_claim'|alias}}This GeoKret is available for adoption. If the current owner gave you the Tracking code plus the Owner code, then you can <a href="%1">claim</a> this GeoKret.{/t}
  {else}
  {t escape=no page="{login_link}"}This GeoKret is available for adoption. Please <a href="%1">login</a> first.{/t}
  {/if}
</div>
{/if}
