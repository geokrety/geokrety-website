{function text}
<p class="text-justify">
    {t escape=no url1="https://geokrety.org"}This document sets the conditions of use of "<a href="%1">geokrety.org</a>" - a voluntarily operated Internet geocaching service available at <a href="%1">geokrety.org</a> (hereinafter referred to as the "Service"). Each person registering an account (hereinafter called a "User") agrees to observe the rules of these terms and conditions, starting from the beginning of the registration procedure.{/t}
</p>

<p class="text-justify">
    {t escape=no}The service is monitored and supervised by a group of volunteer supporters, hereinafter referred to as GK Team. GK Team is not responsible for the content posted by users on the Site or for damages resulting from the use of information from the Service.{/t}
</p>

<p class="text-justify">
    {t escape=no}You must ensure that information published by the User on the Service <strong>must not affect the existing law</strong>. In particular, the content published by the User may not violate the copyright of third parties. All content (including descriptions of the GeoKrety, illustrations and all their entries in the logs) are made available by publishing them on the Service by the User <strong>are licensed under Creative Commons</strong> BY-NC-SA version 2.5, whose complete content is available online at <a href="https://creativecommons.org/licenses/by-nc-sa/2.5/">https://creativecommons.org/licenses/by-nc-sa/2.5/</a>. Public domain content is also admitted. The User is responsible directly to the copyright holder for any violations.{/t}
</p>

<p class="text-justify">
    {t escape=no}We don't provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose. You acknowledge that such information and materials may contain inaccuracies or errors and we expressly exclude liability for any such inaccuracies or errors to the fullest extent permitted by law.{/t}
</p>

<p class="text-justify">
    {t escape=no}Content published by you on the site may not contain vulgar expressions, abusive, or illegal content.{/t}
</p>
{/function}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Terms of use{/t}</h3>
    </div>
    <div class="panel-body">
        {call text}
    </div>
    {if isset($current_user) && !$current_user->hasAcceptedTheTermsOfUse()}
        <div class="panel-footer">
            <form method="POST">
                {call csrf}
                <input type="hidden" id="terms_of_use" name="terms_of_use" value="true">
                <button type="submit" id="termsOfUseAcceptButton" class="btn btn-primary center-block">{t}I agree with the terms{/t}</button>
            </form>
        </div>
    {/if}
</div>
{/block}

{block name=modal_content_only}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Terms of use{/t}</h4>
</div>

<div class="modal-body">
    {call text}
</div>

<div class="modal-footer">
    {call csrf}
    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
    <button type="submit" id="termsOfUseAcceptButton" class="btn btn-primary">{t}I agree with the terms{/t}</button>
</div>
{/block}
