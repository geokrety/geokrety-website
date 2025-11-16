{extends file='emails/base.tpl'}
{assign "fluid" "false"}

{block name=title}{/block}
{block name=preview}{t geokret=$move->geokret->name}New comment on %1{/t}{/block}

{block name=reason}
    {t}You're getting this email because you've enabled instant notifications for GeoKret activities.{/t}
    {* TODO wrong url... it should be new user custom settings page *}
    {t escape=no url={'user_update_email'|alias}}You can disable this by <a href="%1">changing your personal mail preferences</a>.{/t}
{/block}

{block name=content}
    <h5>{t geokret=$move->geokret->name}New comment on %1{/t}</h5>
    <div class="s-3"></div>
    <table class="table table-striped thead-default table-bordered">
        <thead>
        <tr>
            <th>{t}Icon{/t}</th>
            <th>{t}GeoKret{/t}</th>
            <th>{t}Position{/t}</th>
            <th>{t}Comment{/t}</th>
            <th>{t}Author{/t}</th>
            <th>{t}Distance since last move{/t}</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {$move|logicon:false:'html_email' nofilter}
                </td>
                <td>
                    {$move->geokret|gklink nofilter}<br>
                    <small>{$move->geokret->gkid}</small>
                </td>
                <td nowrap>
                    {if !is_null($move->lat) and !is_null($move->lon)}{$move->country|country:'html_email' nofilter}{/if}
                    <small>{$move|cachelink nofilter}</small>
                </td>
                <td>
                    <span title="{$move->comment|markdown:'text'}">{$move->comment|markdown:'text'|truncate:80:"(…)" nofilter}</span>
                </td>
                <td class="text-center text-sm" nowrap>
                    {$move->moved_on_datetime|print_date_iso_format nofilter}
                    <br/>
                    <span class="text-center">{$move->author|userlink:$move->username nofilter}</span>
                </td>
                <td class="text-right text-sm">
                    {if $move->move_type && $move->move_type->isCountingKilometers()}{$move->distance|distance}{/if}
                </td>
            </tr>
        </tbody>
    </table>
    {if $move->comments}
    <table class="table table-striped thead-default table-bordered">
        <thead>
        <tr>
            <th class="w-full">{t}Comment{/t}</th>
            <th>{t}Author{/t}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$move->comments item=comment}
            <tr class="{if $comment->geokret->isMissing()}table-danger {/if}{if $comment->id == $comment_id}table-warning{/if}">
                <td>
                    <span title="{$comment->content|markdown:'text'}">{$comment->content|markdown:'text'|truncate:80:"(…)" nofilter}</span>
                </td>
                <td class="text-center" nowrap>
                    {$comment->created_on_datetime|print_date_iso_format nofilter}
                    <br/>
                    <span class="text-center">{$comment->author|userlink nofilter}</span>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}
    <div class="s-3"></div>

    <div class="text-center">
        <a href="{$move|move_direct_link}" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
            {t}View comment{/t}
        </a>
    </div>

{/block}
