{if $user->isCurrentUser()}
    <a class="anchor" id="users-danger-zone"></a>
    <div class="panel panel-danger" id="userDangerZonePanel">
        <div class="panel-heading">
            {t}Danger zone{/t}
        </div>
        <div class="panel-body">
            <p>{t}Be careful, actions in this section are irreversible.{/t}</p>
            <div class="row">
                <div class="col-sm-12">
                    <button id="userAccountDeleteButton" class="btn btn-danger btn-block" title="{t}Delete your account{/t}" data-toggle="modal" data-target="#modal" data-type="user-delete-account">
                        {fa icon="trash"} {t}Delete my account{/t}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
