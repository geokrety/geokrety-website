<!DOCTYPE html>
<html>
{include file='head.tpl'}
<body>
{include file='header.tpl'}
    <div class="container">
        {include file='banners/is_not_prod.tpl'}
        {include file='banners/contribute.tpl'}
        {include file='banners/debug.tpl'}
        {include file='banners/flash_messages.tpl'}
        {block name=content}{/block}
    </div>
{include file='footer.tpl'}
{include file='navbar.tpl'}
{include file="dialog/base_modal.tpl"}
{include file='javascripts.tpl'}
{include file='github_link.tpl'}
</body>
</html>
