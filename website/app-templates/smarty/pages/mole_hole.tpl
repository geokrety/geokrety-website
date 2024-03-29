{extends file='base.tpl'}

{block name=title}{t}GK mole-holes and GK hotels/motels{/t}{/block}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}GeoKrety mole-hole-system{/t}</h3>
    </div>
    <div class="panel-body">

        <h3>{t}Draft for basic recommendations for the GeoKrety mole-hole-system:{/t}</h3>

        <p>{t escape=no url="https://forum.opencaching.pl/viewtopic.php?t=3593"}This subject <a href="%1">is being discussed here</a>.{/t}</p>
        <ol>
            <li>{t}The system is based on mole-to-mole (peer-to-peer) tunnels. A mole-hole-cache should have a single dedicated destination. A dedicated destination doesn't mean a special cache - an arriving GeoKret may appear in any cache within the destination area.{/t}</li>
            <li>{t}The tunnel should work in both directions. It takes two GeoKrety friends to run a tunnel. Each partner is running a mole-hole-cache, collecting the GeoKrety and sending them over to the other partner, who is spreading the incoming GeoKrety in his homezone.{/t}</li>
            <li>{t distance=500|distance}The tunnel should cross a border or, if it is within a country, the distance between the mole-holes should be at least %1.{/t}</li>
            <li>{t}GeoKrety in a mole-hole are impatient, so no GeoKrety should wait longer than two months for transportation.{/t}</li>
            <li>{t}Each listing of a mole-hole-cache should include{/t}</li>
            <ul>
                <li>{t}a title "Mole-hole (specific name where the cache is located)" e.g. "Mole-hole Pomeranczarnia"{/t}</li>
                <li>{t}a subtitle "GeoKretExpress (departure area) - (destination area)" e.g. "GeoKretExpress Warszawa - Berlin"{/t}</li>
                <li>{t url={'mole_holes'|alias}}a standard text, explaining the mole-hole-system (and maybe a link to <a href="%1">%1</a>?){/t}</li>
                <li>{t}some information about the end of the tunnel (destination area, partner, partner-mole-hole-cache){/t}</li>
                <li>{t}And probably detailed information about the mole-hole-cache and the neighbourhood, how to get there, hint, spoiler and so on.{/t}</li>
            </ul>
        </ol>
        <blockquote>
            <footer>{t escape=no username="Grimpel"}By <cite title="Original source">%1</cite>{/t}</footer>
        </blockquote>

        <h3>{t}Current mole-holes{/t}</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{t}ID{/t}</th>
                        <th>{t}Cache name{/t}</th>
                        <th>{t}Cache type{/t}</th>
                        <th>{t}Country{/t}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $moleholes as $cache => $item}
                    <tr>
                        <td>
                            {if $item.5 == 1}
                            <img src="{GK_CDN_IMAGES_URL}/icons/ok.png" alt="ok" title="{t}Status: ready for search{/t}" />
                            {elseif $item.5 == 3}
                            <img src="{GK_CDN_IMAGES_URL}/icons/error.png" alt="error" title="{t}Status: not ready to search{/t}" />
                            {/if}
                            {$cache}
                        </td>
                        <td><a href="{$item.3}">{$item.0}</a></td>
                        <td>{$item.1}</td>
                        <td nowrap>{$item.4|country nofilter} {$item.2}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

    </div>
</div>


<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}GeoKrety hotels / motels{/t}</h3>
    </div>
    <div class="panel-body">

        <p>{t}A GeoKrety hotel/motel is an easy-to-reach cache, close to an airport/motorway/railroad station, big enough to host some GeoKrety. Cachers may grab or drop GeoKrety. By now, on the OC system, we have registered following GK (or GK/TB) hotels/motels:{/t}</p>

        <h3>{t}Current GK-Hotels/Motels{/t}</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{t}ID{/t}</th>
                        <th>{t}Cache name{/t}</th>
                        <th>{t}Cache type{/t}</th>
                        <th>{t}Country{/t}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $gkhotels as $cache => $item}
                    <tr>
                        <td>
                            {if $item.5 == 1}
                            <img src="{GK_CDN_IMAGES_URL}/icons/ok.png" alt="ok" title="{t}Status: Ready for search{/t}" />
                            {elseif $item.5 == 3}
                            <img src="{GK_CDN_IMAGES_URL}/icons/error.png" alt="error" title="{t}Status: not ready to search{/t}" />
                            {/if}
                            {$cache}
                        </td>
                        <td><a href="{$item.3}">{$item.0}</a></td>
                        <td>{$item.1}</td>
                        <td nowrap>{$item.4|country nofilter} {$item.2}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

    </div>
</div>
{/block}
