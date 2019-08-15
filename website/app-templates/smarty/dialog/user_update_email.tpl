{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Update your email address{/t}</h4>
</div>

<form id="update-email" name="update-email" action="{'user_update_email'|alias}" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
    <div class="modal-body">

        <div class="form-group">
            <label for="inputEmail">{t}Email address{/t}</label>
            <input type="email" class="form-control" id="inputEmail" name="email" placeholder="{t}Email{/t}" value="{$user->email}" required>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" name="daily_mails" {if $user->daily_mails}checked{/if}> {t}Yes, I want to receive email alerts (sent once a day).{/t}
            </label>
        </div>

        <hr />
        <p>
            <strong>{t}The main purpose of collecting email is to permit password recovery.{/t}</strong>
        </p>
        <p>
            <em>
                {t}Email alerts may contain any of the following:{/t}
                {t escape=no}<ul>
                    <li>GeoKrety.org news</li>
                    <li>Recent logs on:</li>
                    <ul>
                        <li>your own GeoKrety</li>
                        <li>GeoKrety that you watch</li>
                        <li>any GeoKrety logged near your home location</li>
                    </ul>
                    <li>Comments posted to any of the following:</li>
                    <ul>
                        <li>your own GeoKrety</li>
                        <li>GeoKrety that you watch</li>
                        <li>your logs</li>
                        <li>your comments</li>
                        <li>news posts you have are subscribed to</li>
                    </ul>
                </ul>{/t}
            </em>
        </p>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-info">{t}Change{/t}</button>
    </div>
</form>
{/block}
