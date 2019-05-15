<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">{t}Change your password{/t}</h3>
  </div>
  <div class="panel-body">

    <form name="comment" action="/edit.php?co=haslo" method="post" class="form-horizontal" id="passwordChangeForm">
      <div class="modal-body">

        <div class="row">
          <div class="col-md-6">

            <div class="form-group">
              <label for="inputPasswordOld" class="col-sm-2 control-label">Current password</label>
              <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPasswordOld" name="inputPasswordOld" placeholder="Old password" required>
              </div>
            </div>
            <hr />
            <div class="form-group">
              <label for="inputPasswordNew" class="col-sm-2 control-label">New password</label>
              <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPasswordNew" name="inputPasswordNew" placeholder="New password" required>
              </div>
            </div>

            <div class="form-group">
              <label for="inputPasswordConfirm" class="col-sm-2 control-label">Confirm password</label>
              <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPasswordConfirm" name="inputPasswordConfirm" placeholder="Confirm password" required>
              </div>
            </div>

          </div>
          <div class="col-md-5 col-md-offset-1">

            <h4>{t}Read more about choosing good passwords:{/t}</h4>
            <ul>
              <li><a href="http://hitachi-id.com/password-manager/docs/choosing-good-passwords.html" target="_blank">{t}Choosing Good Passwords -- A User Guide{/t}</a> {fa icon="external-link"}</li>
              <li><a href="http://www.csoonline.com/article/220721/how-to-write-good-passwords" target="_blank">{t}How to Write Good Passwords{/t}</a> {fa icon="external-link"}</li>
              <li><a href="http://en.wikipedia.org/wiki/Password_strength" target="_blank">{t}Password strength{/t}</a> {fa icon="external-link"}</li>
            </ul>

          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <a class="btn btn-default" href="{$user->geturl()}" title="{t}Back to user page{/t}">
                {t}Dismiss{/t}
              </a>
              <button type="submit" class="btn btn-primary">{t}Change{/t}</button>
            </div>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>
