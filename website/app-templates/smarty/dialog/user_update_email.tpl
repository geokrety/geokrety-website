{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Update your email address{/t}</h4>
</div>

<form id="update-email" name="update-email" action="{'user_update_email'|alias}" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
    <div class="modal-body">

        <div class="form-group">
            <label for="inputEmail">{t}Email address{/t}</label>
            <input type="email" class="form-control" id="inputEmail" name="email" placeholder="{t}Email address{/t}" value="{$currentUser->email}" required>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" id="dailyMailsCheckbox" name="daily_mails" {if $currentUser->daily_mails}checked{/if}> {t}Yes, I want to receive email alerts (sent once a day).{/t}
            </label>
        </div>

        <hr />
        <p>
            <strong>{t}The main purpose of collecting email is to permit password recovery.{/t}</strong>
        </p>
        <p>
            <em>
                {t}Email alerts may contain any of the following:{/t}
                <ul>
                    <li>{t}GeoKrety.org news{/t}</li>
                    <li>{t}Recent logs on:{/t}</li>
                    <ul>
                        <li>{t}your own GeoKrety{/t}</li>
                        <li>{t}GeoKrety that you watch{/t}</li>
                        <li>{t}any GeoKrety logged near your home location{/t}</li>
                    </ul>
                    <li>{t}Comments posted to any of the following:{/t}</li>
                    <ul>
                        <li>{t}your own GeoKrety{/t}</li>
                        <li>{t}GeoKrety that you watch{/t}</li>
                        <li>{t}your logs{/t}</li>
                        <li>{t}your comments{/t}</li>
                        <li>{t}news posts you have are subscribed to{/t}</li>
                    </ul>
                </ul>
            </em>
        </p>

    </div>
    <div class="modal-footer">
        {call csrf}
        <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $currentUser->id)}" title="{t}Back to user page{/t}" data-dismiss="modal">
            {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-info">{t}Change{/t}</button>
    </div>
</form>
{/block}
