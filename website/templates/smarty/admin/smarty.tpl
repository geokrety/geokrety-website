<ol class="breadcrumb">
  <li><a href="/">Home</a></li>
  <li><a href="/_admin.php">Admin</a></li>
  <li class="active">Smarty</li>
</ol>

<h1>Erase smarty templates</h1>

<form method='post'>
  <input type='hidden' name='formname' value='clear_all_cache'>
  <div class="form-group">
    <button type="submit" class="btn btn-primary btn-lg btn-block">Clear cached pages</button>
  </div>
</form>

<form method='post'>
  <input type='hidden' name='formname' value='clear_compiled_tpl'>
  <div class="form-group">
    <button type="submit" class="btn btn-primary btn-lg btn-block">Clear compiled templates</button>
  </div>
</form>
