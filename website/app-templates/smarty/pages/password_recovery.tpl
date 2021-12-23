{extends file='base.tpl'}

{block name=title}{t}Password recovery{/t}{/block}

{include file='macros/recaptcha.tpl'}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        {t}Password recovery{/t}
    </div>
    <div class="panel-body">

        <form class="form-horizontal" action="" method="post" id="formPasswordRecovery" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>


            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <p class="form-control-static">{t}If you have validated your mail address in the past, then you can recover it easily. Else, you'll have to create another account, sorry.{/t}</p>
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="col-sm-2 control-label">{t}Email address{/t}</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="email" name="email" placeholder="{t}Email address{/t}" value="{$user->email}" required>
                </div>
            </div>

            {call recaptcha}

            <div class="form-group">
                {call csrf}
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" id="sendRecoveryLinkButton" class="btn btn-primary">{t}Send me a recovery link{/t}</button>
                </div>
            </div>
        </form>

    </div>
</div>
{/block}
