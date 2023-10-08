<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>GeoKrety: {block name=title}{/block}</title>
    <meta name="title" content="GeoKrety"/>
    <meta name="description" content="{t}Open source item tracking for all geocaching platforms{/t}"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="https://geokrety.org/"/>
    <meta property="og:title" content="GeoKrety"/>
    <meta property="og:description" content="{t}Open source item tracking for all geocaching platforms{/t}"/>
    <meta property="twitter:url" content="https://geokrety.org/"/>
    <meta property="twitter:title" content="GeoKrety"/>
    <meta property="twitter:description" content="{t}Open source item tracking for all geocaching platforms{/t}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    {\Assets::instance()->addCss(GK_CDN_BOOTSTRAP_CSS) && ''}
    {\Assets::instance()->addCss(GK_CDN_FONT_AWESOME_CSS) && ''}
    {\Assets::instance()->addCss(GK_CDN_FLAG_ICON_CSS) && ''}
    {\Assets::instance()->addCss(GK_CDN_LIBRARIES_PARSLEY_CSS_URL) && ''}
    {\Assets::instance()->addCss(GK_CDN_MAGNIFIC_POPUP_CSS) && ''}
    {\Assets::instance()->addCss('css/app.scss') && ''}
    {\Assets::instance()->renderGroup(\Assets::instance()->getAssets('head')) nofilter}

    <link rel="apple-touch-icon" sizes="180x180" href="{GK_CDN_IMAGES_URL}/favicon/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="{GK_CDN_IMAGES_URL}/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="{GK_CDN_IMAGES_URL}/favicon/favicon-16x16.png" />
    <link rel="manifest" href="{GK_CDN_IMAGES_URL}/favicon/manifest.json" />
    <link rel="mask-icon" href="{GK_CDN_IMAGES_URL}/favicon/safari-pinned-tab.svg" color="#5bbad5" />
    <link rel="shortcut icon" href="{GK_CDN_IMAGES_URL}/favicon/favicon.ico" />
    <meta name="msapplication-config" content="{GK_CDN_IMAGES_URL}/favicon/browserconfig.xml" />
    <meta name="theme-color" content="#ffffff" />
{if \Multilang::instance()->current === 'inline-translation'}
    {include file="js/_crowdin.tpl.html"}
{/if}
</head>
