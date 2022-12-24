{extends file='base.tpl'}

{block name=title}{t}Contact us{/t}{/block}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Contact us{/t}</h3>
    </div>
    <div class="panel-body">

        <p>
            {t}If you have any suggestion or bug reports feel free to write us.{/t}
        </p>

        <div class="row">
            <label class="col-sm-2 control-label">{t}Helpdesk{/t}</label>
            <div class="col-sm-6">
                <a href="https://support.geokrety.org" alt="{t}Helpdesk portal{/t}" >{t}Helpdesk portal{/t}</a>
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">{t}Email{/t}</label>
            <div class="col-sm-6">
                <img src="{GK_CDN_IMAGES_URL}/support-email.svg" alt="mail" class="img-responsive" />
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">{t}Or via the user profile{/t}</label>
            <div class="col-sm-6">
                <a href="{'mail_to_user'|alias:sprintf('@userid=%d', 26422)}">kumy</a> (en français, in english)<br />
                <a href="{'mail_to_user'|alias:sprintf('@userid=%d', 35313)}">bsllm</a> (en français, in english)<br />
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">
                <div data-toggle="tooltip" title="International Relay Chat">{t}IRC{/t}</div>
            </label>
            <div class="col-sm-6">
                <a href="https://webchat.freenode.net/?channels=geokrety">Freenode - #GeoKrety</a>
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">{t}Twitter{/t}</label>
            <div class="col-sm-6">
                <a href="https://twitter.com/geokrety">@GeoKrety</a>
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">{t}Public forum{/t}</label>
            <div class="col-sm-6">
                <a href="https://groups.google.com/forum/#!forum/geokrety">International - English - GeoKrety Google group</a><br />
                <a href="https://groups.google.com/forum/#!forum/geokrety-french">Francophone - Français - GeoKrety Google group</a>
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">{t}Wanna sign or encrypt your mails?{/t}</label>
            <div class="col-sm-6">
                <p>{t escape=no gpg_url=GK_CDN_GPGKEY_URL gpg_id=GK_CDN_GPGKEY_ID}Our public PGP/GPG key <a href="%1">is here (%2)</a>{/t}</p>
            </div>
        </div>
    </div>
</div>
{/block}
