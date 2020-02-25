{extends file='base.tpl'}

{block name=content}
    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">External tools</div>
                <div class="panel-body">
                    <ul>
                        <li>
                            <a href="{GK_SITE_BASE_SERVER_URL}/adminer">Adminer</a>
                        </li>
                        <li>
                            <a href="{GK_MINIO_SERVER_URL_EXTERNAL}">Minio</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">Generated</div>
                <div class="panel-body">
                    <ul>
                        <li>
                            <a href="{'admin_rebuild_templates'|alias}">Rebuild all templates</a>
                        </li>
                        <li>
                            <a href="{'admin_rebuild_translation'|alias}">Rebuild gettext files</a>
                        </li>
                        <li>
                            <a href="{'admin_assets_clear'|alias}">Clear assets</a>
                        </li>
                        <li>
                            <a href="{'admin_s3_prune_pictures'|alias}">Prune never uploaded pictures</a>
                        </li>
                    </ul>
                    <div class="alert alert-info" role="warning">If the service is scaled more than 1, then actions will reach only one instance at a time.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">Admin tools</div>
                <div class="panel-body">
                    <ul>
                        <li>
                            Add news
                        </li>
                        <li>
                            Change user name
                        </li>
                        <li>
                            Change GeoKret ownership
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">…</div>
                <div class="panel-body">
                    <ul>
                        <li>
                            …
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
{/block}
