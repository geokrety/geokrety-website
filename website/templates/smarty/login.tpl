
<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">{t}Login{/t}</h3>
      </div>
      <div class="panel-body">
        <form action="/longin.php{if $goto}?goto={$goto}{/if}" method="post" class="form-horizontal">

          <div class="form-group">
            <label for="inputUsername" class="col-sm-2 control-label">{t}Username{/t}</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="inputUsername" name="login" placeholder="{t}Username{/t}" maxlength="30">
            </div>
          </div>
          <div class="form-group">
            <label for="inputPassword" class="col-sm-2 control-label">{t}Password{/t}</label>
            <div class="col-sm-10">
              <input type="password" class="form-control" id="inputPassword" name="haslo1" placeholder="{t}Password{/t}" maxlength="80">
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <div class="checkbox">
                <label>
                  <input id="remember" name="remember" type="checkbox"> {t}Remember me{/t}
                </label>
                <p class="help-block">
                  {t escape=no url="/help.php#cookies"}We are using cookies only for storing login information and language preferences. Read more about our <a href="%1">cookies policy</a>.{/t}
                </p>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button type="submit" class="btn btn-primary">{t}Sign in{/t}</button>
              <div class="pull-right">
                <a href="/new_password.php">{t}Forgot your password?{/t}</a>
              </div>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
</div>
