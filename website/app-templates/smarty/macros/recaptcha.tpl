{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}
{function recaptcha class="form-group"}
    {if GK_GOOGLE_RECAPTCHA_PUBLIC_KEY}
        <hr>
        <div class="{$class}">
            <div class="g-recaptcha" data-sitekey="{GK_GOOGLE_RECAPTCHA_PUBLIC_KEY}" id="recaptcha_wrapper"></div>
        </div>
    {/if}
{/function}
