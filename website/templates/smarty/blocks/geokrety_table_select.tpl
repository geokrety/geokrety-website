{include file='macros/converters.tpl'}

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
        <tbody id="geokretyListTable">
            {foreach from=$geokrety item=geokret}
            <tr>
                <td>
                    <button class="btn btn-primary" name="btnChooseGK" data-trackingcode="{$geokret->trackingCode}">Choose</button>
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
                    {print_date date=$geokret->lastLog->ruchData}
                    {else}
                    {print_date date=$geokret->datePublished}
                    {/if}
                </td>
                <td>
                    {if $geokret->hasCurrentUserSeenGeokretId()}
                    <input type="checkbox" name="geokretySelected" data-trackingcode="{$geokret->trackingCode}" />
                    {/if}
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
