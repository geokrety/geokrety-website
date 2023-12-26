{extends file='emails/base.tpl'}
{assign "fluid" "true"}

{block name=title}{/block}
{block name=preview}{t}Your GeoKrety watchlist{/t}{/block}

{block name=reason}
    {t}You're getting this email because you've choose to receive daily mail updates.{/t}
    {t escape=no url={'user_update_email'|alias}}You can disable this by <a href="%1">changing your personal mail preferences</a>.{/t}
{/block}

{block name=content}

    <p>{t}Here is what changed recently.{/t}</p>
    <div class="s-3"></div>
    {if $news}
        <h5>{t}Latest news{/t}</h5>
        {foreach from=$news item=item}
            <div class="s-3"></div>
            <h6>{$item->title}</h6>
            <p class="text-sm">{$item->content nofilter}</p>
        {/foreach}
    {/if}

    {if $gk_near_home}
    <div class="s-6"></div>
    <h5>{t}GeoKrety dropped in your observation area{/t}</h5>
    {if isset($gk_near_home_img)}{* Prevent crash if webservice is absent *}
    <div class="s-3"></div>
    <a href="{'geokrety_map'|alias}" title="{t}Open interactive map{/t}"><img src="cid:{$gk_near_home_img}" alt="{t}Observation area map{/t}" class="img-fluid max-w-150 align-center text-center"></a>
    {/if}
    <div class="s-3"></div>
    <table class="table table-striped thead-default table-bordered">
        <thead>
        <tr>
            <th>{t}GeoKret{/t}</th>
            <th>{t}Position{/t}</th>
            <th>{t}Comment{/t}</th>
            <th>{t}Author{/t}</th>
            <th>{t}Home distance{/t}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$gk_near_home item=geokret}
            <tr>
                <td>
                    {$geokret|gklink nofilter}<br>
                    <small>{$geokret->gkid}</small>
                </td>
                <td nowrap>
                    {if !is_null($geokret->lat) and !is_null($geokret->lon)}{$geokret->country|country:'html' nofilter}{/if}
                    {$geokret->last_position|cachelink nofilter}
                </td>
                <td>
                    <span title="{$geokret->last_position->comment|markdown:'text'}">{$geokret->last_position->comment|markdown:'text'|truncate:80:"(…)" nofilter}</span>
                </td>
                <td class="text-center text-sm" nowrap>
                    {$geokret->moved_on_datetime|print_date nofilter}
                    <br/>
                    <span class="text-center">{$geokret->author|userlink:$geokret->author_username nofilter}</span>
                </td>
                <td class="text-right text-sm">
                    {($geokret->home_distance)|distance}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}

    {if $moves}
    <div class="s-6"></div>
    <h5>{t}My GeoKrety latest moves{/t}</h5>
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
        {foreach from=$moves item=move}
            <tr>
                <td>
                    {$move|logicon nofilter}
                </td>
                <td>
                    {$move->geokret|gklink nofilter}<br>
                    <small>{$move->geokret->gkid}</small>
                </td>
                <td nowrap>
                    {if !is_null($move->lat) and !is_null($move->lon)}{$move->country|country:'html' nofilter}{/if}
                    {$move|cachelink nofilter}
                </td>
                <td>
                    <span title="{$move->comment|markdown:'text'}">{$move->comment|markdown:'text'|truncate:80:"(…)" nofilter}</span>
                </td>
                <td class="text-center text-sm" nowrap>
                    {$move->moved_on_datetime|print_date nofilter}
                    <br/>
                    <span class="text-center">{$move->author|userlink:$move->username nofilter}</span>
                </td>
                <td class="text-right text-sm">
                    {if $move->move_type && $move->move_type->isCountingKilometers()}{$move->distance|distance}{/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}

    {if $watched}
    <div class="s-6"></div>
    <h5>{t}Watched GeoKrety latest moves{/t}</h5>
    <div class="s-3"></div>
    <table class="table table-striped thead-default table-bordered">
        <thead>
        <tr>
            <th>{t}Icon{/t}</th>
            <th>{t}GeoKret{/t}</th>
            <th>{t}Position{/t}</th>
            <th>{t}Comment{/t}</th>
            <th>{t}Author{/t}</th>
            <th>{t}Distance{/t}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$watched item=move}
            <tr>
                <td>
                    {$move|logicon nofilter}
                </td>
                <td>
                    {$move->geokret|gklink nofilter}<br>
                    <small>{$move->geokret->gkid}</small>
                </td>
                <td nowrap>
                    {if !is_null($move->lat) and !is_null($move->lon)}{$move->country|country:'html' nofilter}{/if}
                    <small>{$move|cachelink nofilter}</small>
                </td>
                <td>
                    <span title="{$move->comment|markdown:'text'}">{$move->comment|markdown:'text'|truncate:80:"(…)" nofilter}</span>
                </td>
                <td class="text-center text-sm" nowrap>
                    {$move->moved_on_datetime|print_date nofilter}
                    <br/>
                    <span class="text-center">{$move->author|userlink:$move->username nofilter}</span>
                </td>
                <td class="text-right text-sm">
                    {if $move->move_type && $move->move_type->isCountingKilometers()}{$move->distance|distance}{/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}

    {if $comments}
    <div class="s-6"></div>
    <h5>{t}Latest move comments{/t}</h5>
    <div class="s-3"></div>
    <table class="table table-striped thead-default table-bordered">
        <thead>
        <tr>
            <th>{t}GeoKret{/t}</th>
            <th class="w-full">{t}Comment{/t}</th>
            <th>{t}Author{/t}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$comments item=comment}
            <tr class="{if $comment->geokret->isMissing()}table-danger{/if}">
                <td nowrap>
                    {if $comment->type === 0}📄{else}❗{/if}
                    {$comment->geokret|gklink nofilter}<br>
                    <small>{$comment->geokret->gkid}</small>
                </td>
                <td>
                    <span title="{$comment->content|markdown:'text'}">{$comment->content|markdown:'text'|truncate:80:"(…)" nofilter}</span>
                </td>
                <td class="text-center" nowrap>
                    {$comment->created_on_datetime|print_date nofilter}
                    <br/>
                    <span class="text-center">{$comment->author|userlink nofilter}</span>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}
{/block}
