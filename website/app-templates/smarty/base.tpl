<!DOCTYPE html>
<!--
// 🕷️ You discovered The Source Dweller
// 👁️ A quiet crawler hidden in the HTML depths. It does not walk the trails nor show in the UI —
// it weaves its web between the tags and comments.
// 🔎 Only those who inspect the code itself will ever notice it — and now, you've found it!
// ✨ Log your discovery: https://geokrety.org/m/TAGSGK
-->
<html lang="{\Multilang::instance()->current}">
{include file='head.tpl'}
{include file='macros/csrf.tpl'}
<body>
{include file='header.tpl'}
    <div class="container">
        {include file='banners/is_not_prod.tpl'}
        {include file='banners/contribute.tpl'}
        {include file='banners/user_email_invalid.tpl'}
        {include file='banners/user_email_pending_validation.tpl'}
        {include file='banners/user_password_missing.tpl'}
        {include file='banners/flash_messages.tpl'}
        {block name=content}{/block}
        <div id ="spinner-center" style="top:30%;left:50%;z-index: 3000000000;"></div>
    </div>
{include file='footer.tpl'}
{include file='navbar.tpl'}
{*include file='js/navbar-lateral.tpl'*}
{include file="dialog/base_modal.tpl"}
{include file='javascripts.tpl'}
{include file='github_link.tpl'}
</body>
</html>
