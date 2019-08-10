{if GK_ENVIRONMENT === 'dev'}
<div class="alert alert-info" role="alert">
  LANG: {$smarty.get|print_r}<br />
  DETECTED LANG: {$detected_lang}<br />
  DEFINED LANG: {LANGUAGE}<br />
</div>
{/if}
