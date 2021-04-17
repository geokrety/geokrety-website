<preview>{block name=preview}{/block}</preview>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body class="bg-light">
<div class="container{if $fluid|default:false}-fluid{/if}">

    <div class="stack-y w-full">
        <div class="header">
            <div class="s-3"></div>
            <h1 class="logo align-center text-center">
                <a href="{'home'|alias}">
                    Geo<span class="black">Krety</span>.org
                </a>
            </h1>
            <div class="s-3"></div>
            <div class="to-table w-full subline">
                <p class="align-center text-center text-xs">
                    {t}Open source item tracking for all geocaching platforms{/t}
                </p>
            </div>
            <div class="s-3"></div>
        </div>

        <div class="s-3"></div>

        <p class="text-3xl align-center text-center">{block name=title}{/block}</p>
        <p>{t username=$user->username}Hi %1,{/t}</p>

        <div class="s-3"></div>
        {block name=content}{/block}

        <div class="s-6"></div>
        <p>
            {t escape=no}Sincerely,<br>The GeoKrety Team{/t}
        </p>

    </div>

    <hr>

    <p class="text-justify text-xs text-secondary">
        {block name=reason}{/block}<br>
        {t escape=no email=GK_SITE_EMAIL subject='%3Cyour%20subject%3E'}If you never created an account on GeoKrety.org, please <a href="mailto:%1?subject=%2">contact us</a>.{/t}<br>
        <br>
        {include file='elements/version.tpl'}
    </p>

</div>
</body>
</html>
