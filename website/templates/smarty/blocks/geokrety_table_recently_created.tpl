<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th>{t}ID{/t}</th>
        <th class="text-center">{t}Owner{/t}</th>
        <th class="text-center">{t}Last move{/t}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$recent_geokrety item=geokret}
      <tr>
        <td>{call gk_icon gk_type=$geokret->type}</td>
        <td>
          {gklink gk=$geokret}{gkavatar gk=$geokret}<br />
          <small><span title="{$geokret->name}">{$geokret->name|truncate:30:"…"}</span></small>
        </td>
        <td class="text-center">
          {userlink user=$geokret->owner()}
        </td>
        <td class="text-center" nowrap>
          <span title="{$geokret->datePublished}">{Carbon::parse($geokret->datePublished)->diffForHumans()}</span><br />
        </td>
        <td>
        </td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>
