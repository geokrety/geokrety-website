{extends file='emails/base.tpl'}
{assign "fluid" "false"}

{block name=title}{/block}
{block name=preview}{t geokret=$geokret->name}Someone loved your %1! ❤️{/t}{/block}

{block name=reason}
    {t}You're getting this email because you've enabled instant notifications for loves.{/t}
    {t escape=no url={'user_update_email'|alias}}You can disable this by <a href="%1">changing your personal mail preferences</a>.{/t}
{/block}

{block name=content}
    <h5>{t geokret=$geokret->name}Someone loved your %1! ❤️{/t}</h5>
    <div class="s-3"></div>
    <table class="table table-striped thead-default table-bordered">
        <thead>
        <tr>
            <th>{t}GeoKret{/t}</th>
            <th>{t}Current position{/t}</th>
            <th>{t}Type{/t}</th>
            <th>{t}Loved by{/t}</th>
            <th>{t}Date{/t}</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {$geokret|gklink nofilter}<br>
                    <small>{$geokret->gkid}</small>
                </td>
                <td nowrap>
                    {if !is_null($geokret->lat) and !is_null($geokret->lon)}{$geokret->country|country:'html_email' nofilter}{/if}
                    <small>{$geokret->last_position|cachelink nofilter}</small>
                </td>
                <td>
                    {$geokret->type}
                </td>
                <td class="text-center text-sm" nowrap>
                    {$lover|userlink nofilter}
                </td>
                <td class="text-center text-sm" nowrap>
                    {$love->created_on_datetime|print_date_iso_format nofilter}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="s-3"></div>

    <div class="text-center">
        <a href="{$geokret|gklink}" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; background-color: #d9534f; color: white; text-decoration: none; border-radius: 5px;">
            {t}View GeoKret details{/t}
        </a>
    </div>

{/block}
