<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{t}languages{/t}</th>
                <th>{t}group service{/t}</th>
                <th>{t}group name{/t}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$social_groups item=group}
            <tr>
                <td>
                    {foreach from=$group.lang item=lang name=languages}
                    {\GeoKrety\Service\LanguageService::getLanguageByAlpha2($lang)->getLocalName()}
                    {if !$smarty.foreach.languages.last}, {/if}
                    {/foreach}
                </td>
                <td>{$group.service}</td>
                <td><a href="{$group.link}" target="_blank">{$group.title} {fa icon="external-link"}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
