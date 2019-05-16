<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{t}Contact user{/t}</h3>
            </div>
            <div class="panel-body">
                <form method="post" class="form-horizontal">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">{t}From{/t}</label>
                        <div class="col-sm-10">
                            <p class="form-control-static">{$userFrom->username}</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">{t}To{/t}</label>
                        <div class="col-sm-10">
                            <p class="form-control-static">{t username=$userTo->username lang={language lang=$userTo->language}}%1 (speak %2){/t}</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputSubject" class="col-sm-2 control-label">{t}Subject{/t}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control maxl" id="inputSubject" name="subject" placeholder="{t}Message subject{/t}" maxlength="75" value="{if isset($smarty.post.subject)}{$smarty.post.subject}{else if isset($subject)}{$subject}{/if}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputUsername" class="col-sm-2 control-label">{t}Message{/t}</label>
                        <div class="col-sm-10">
                            <textarea class="form-control maxl" rows="5" name="message" placeholder="{t}Message to user{/t}" maxlength="5120">{if isset($smarty.post.message)}{$smarty.post.message}{/if}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2">
                            <div class="g-recaptcha" data-sitekey="{$GOOGLE_RECAPTCHA_PUBLIC_KEY}" id="recaptcha_wrapper"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">{t}Send message{/t}</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
