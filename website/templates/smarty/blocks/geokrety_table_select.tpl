
<div class="table-responsive pre-scrollable">
    <table class="table table-striped">
        <thead>
            <tr>
                <th></th>
                <th>{t}ID{/t}</th>
                <th class="text-center">{t}Owner{/t}</th>
                <th><input type="checkbox" id="geokretySelectAll" title="Select all" /></th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$geokrety item=geokret}
            <tr>
                <td>
                    <button class="btn btn-primary" type="submit">Choose</button>
                </td>
                <td>
                    {gklink gk=$geokret} {gkavatar gk=$geokret}<br />
                    <small><span title="{$geokret->name}">{$geokret->name|truncate:30:"…"}</span></small>
                </td>
                <td class="text-center" class="text-center" nowrap>
                    {userlink user=$geokret->owner()}
                    <br />
                    {logicon gk=$geokret}
                    {if $geokret->lastLog->ruchData}
                    {Carbon::parse($geokret->lastLog->ruchData)->diffForHumans()}
                    {else}
                    {Carbon::parse($geokret->datePublished)->diffForHumans()}
                    {/if}
                </td>
                <td>
                    {if $geokret->hasCurrentUserSeenGeokretId()}
                    <input type="checkbox" name="geokretySelected" />
                    {/if}
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>

<div class="alert alert-warning" role="alert">Not yet implemented!</div>
