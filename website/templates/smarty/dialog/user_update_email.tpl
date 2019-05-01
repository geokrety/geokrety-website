<div class="modal-header alert-danger">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title" id="modalLabel">{t}Update your email address{/t}</h4>
</div>
<form name="comment" action="/edit.php?co=email" method="post">
  <div class="modal-body">

    <div class="form-group">
      <label for="inputEmail">Email address</label>
      <input type="email" class="form-control" id="inputEmail" name="email" placeholder="Email" value="{$user->email}">
    </div>

    <div class="checkbox">
      <label>
        <input type="checkbox" name="subscribe" {if $user->acceptEmail}checked{/if}> {t}Yes, I want to receive email alerts (sent once a day).{/t}
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
        </ul>

      </em>
    </p>

  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
    <button type="submit" class="btn btn-danger">{t}Change{/t}</button>
  </div>
</form>
