{if GK_ENVIRONMENT === 'dev'}
<div class="alert alert-info" role="alert">
  LANG: {$smarty.get|print_r}<br />
  DETECTED LANG: {$detected_lang}<br />
  DEFINED LANG: {LANGUAGE}<br />
  IS_LOGGED_IN: [{$f3->get('SESSION.IS_LOGGED_IN')}]<br />
  CURRENT_USER: [{$f3->get('SESSION.CURRENT_USER')}]<br />
  {$smarty.session|print_r}<br />
</div>
{/if}
