<preview>{block name=preview}{/block}</preview>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body class="bg-light">
<div class="container-fluid">

    <div class="stack-y w-full">
        <div class="header">
            <div class="s-3"></div>
            <h1 class="logo align-center">
                <a href="{'home'|alias}">
                    Geo<span class="black">Krety</span>.org
                </a>
            </h1>
            <div class="s-3"></div>
            <div class="to-table w-full subline">
                <p class="align-center text-xs">
                    {t}Open source item tracking for all geocaching platforms{/t}
                </p>
            </div>
            <div class="s-3"></div>
        </div>

        <div class="s-3"></div>

        <p class="text-3xl align-center">{block name=title}{/block}</p>
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
        {include file='elements/version.tpl'}
    </p>

</div>
</body>
</html>
