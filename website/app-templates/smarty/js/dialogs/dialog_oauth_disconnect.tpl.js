{if GK_OPAUTH_ACTIVE}
$('#modal').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let typeName = button.data('type');
    let name = button.data('oauth-provider-name');

    if (typeName === 'user-oauth-connect') {
        modalLoad("{'opauth_detach'|alias:'strategy=%STRATEGY%'}".replace('%STRATEGY%', name));
    }
});
{/if}
