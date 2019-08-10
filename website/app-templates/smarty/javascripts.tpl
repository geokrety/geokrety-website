<script type="text/javascript" src="{GK_CDN_LIBRARIES_URL}/jquery/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_URL}/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_URL}/moment.js/2.22.0/moment.min.js"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_URL}/bootstrap-maxlength/1.7.0/bootstrap-maxlength.min.js"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_URL}/preview-image-jquery/1.0/preview-image.min.js"></script>
{block name=js}{/block}

<script type="text/javascript">
    (function($) {
        $( document ).ready( function () {
            {include file="js/modal.tpl.js"}
            {include file="js/maxlenght.tpl.js"}
            {include file="js/tooltips.tpl.js"}
            {if ! $f3->get('SESSION.IS_LOGGED_IN')}{include 'js/dialog_login.js.tpl'}{/if}
            {block name=javascript}{/block}
        });
    })(jQuery);
</script>
