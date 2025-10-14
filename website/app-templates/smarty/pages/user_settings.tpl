{extends file='base.tpl'}

{block name=title}{t}My Settings{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_DATATABLE_I18N) && ''}

{block name=content}
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{t}Personal Settings{/t}</h3>
            </div>
            <div class="panel-body">
                <p>{t}Here you can customize your personal preferences. Any setting you change will override the default site value.{/t}</p>
                <p>{t}Settings marked with a badge have been customized by you.{/t}</p>

                <table id="userCustomSettingsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{t}Setting{/t}</th>
                            <th>{t}Description{/t}</th>
                            <th>{t}Default Value{/t}</th>
                            <th>{t}Current Value{/t}</th>
                            <th>{t}Actions{/t}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {* Table will be populated by JavaScript *}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{/block}

{block name=javascript}
{include file='js/dialogs/dialog_user_settings.tpl.js'}
{include file='js/users/user_settings.tpl.js'}
{/block}
