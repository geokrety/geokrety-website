{extends file='base.tpl'}

{block name=content}
    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">External tools</div>
                <div class="panel-body">
                    <ul>
                        <li>
                            <a href="{ADMIN_SERVICE_ADMINER_URL}" target="_blank">Adminer</a>
                        </li>
                        <li>
                            <a href="{ADMIN_SERVICE_PGADMIN_URL}" target="_blank">PGAdmin</a>
                        </li>
                        <li>
                            <a href="{GK_MINIO_SERVER_URL_EXTERNAL}" target="_blank">Minio</a>
                        </li>
                        <li>
                            <a href="{ADMIN_SERVICE_GRAFANA_URL}" target="_blank">Grafana</a>
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
                        <li>
                            <a href="{'admin_s3_prune_pictures'|alias}">Prune never uploaded pictures</a>
                        </li>
                        <li>
                            <a href="{'admin_scripts'|alias}">Manage scripts</a>
                        </li>
                        <li>
                            <a href="{'admin_users_list'|alias}">Manage users</a>
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
                            <a href="http://localhost:8080/">SVG to PNG</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
{/block}
