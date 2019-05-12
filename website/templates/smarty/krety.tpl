<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>GeoKrety: {$title}</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="{$cdnUrl}/libraries/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="{$cssUrl}/krety-v2.css" media="screen" />
  <link rel="stylesheet" href="{$cssUrl}/flag-icon.min.css" media="screen" />
  <link rel="stylesheet" href="{$cdnUrl}/libraries/font-awesome/4.7.0/css/font-awesome.min.css">
  {if count($css)}
  {foreach from=$css item=item}
  <link rel="stylesheet" href="{$item nofilter}">
  {/foreach}
  {/if}

  <link rel="apple-touch-icon" sizes="180x180" href="{$imagesUrl}/favicon/apple-touch-icon.png" />
  <link rel="icon" type="image/png" sizes="32x32" href="{$imagesUrl}/favicon/favicon-32x32.png" />
  <link rel="icon" type="image/png" sizes="16x16" href="{$imagesUrl}/favicon/favicon-16x16.png" />
  <link rel="manifest" href="{$imagesUrl}/favicon/manifest.json" />
  <link rel="mask-icon" href="{$imagesUrl}/favicon/safari-pinned-tab.svg" color="#5bbad5" />
  <link rel="shortcut icon" href="{$imagesUrl}/favicon/favicon.ico" />
  <meta name="msapplication-config" content="{$imagesUrl}/favicon/browserconfig.xml" />
  <meta name="theme-color" content="#ffffff" />
</head>

<body>
  <header>
    <p class="logo"><a href="/">Geo<span class="black">Krety</span>.org</a></p>
    <p class="subline">{$site_punchline}</p>
    <span class="bg"></span>
    <img class="sun" src="{$imagesUrl}/header/sun.svg">
  </header>

  <div class="container">
{if not IS_PROD}
    <div class="alert alert-danger" role="alert">
    <b>{t escape=no}This is not the production instance. If you are not a tester, then you probably whish to go to our <a href="https://geokrety.org">production website</a>.{/t}</b>
    </div>
{/if}

    {if count($alert_msgs)}
    {foreach from=$alert_msgs item=alert_msg}
    <div class="alert alert-{$alert_msg.level} alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="{t}Close{/t}"><span aria-hidden="true">&times;</span></button>
      {$alert_msg.message nofilter}
    </div>
    {/foreach}
    {/if}

    {if isset($content_template) and $content_template}
    {include file=$content_template}
    {/if}
    {$content nofilter}
  </div>
  {include file='footer.tpl'}

  {include file='navbar.tpl'}

  {if isset($ldjson) and $ldjson}
  {$ldjson nofilter}
  {/if}

  {include file="dialog/base_modal.tpl"}

  <script type="text/javascript" src="{$cdnUrl}/libraries/jquery/jquery-3.3.1.min.js"></script>
  <script type="text/javascript" src="{$cdnUrl}/libraries/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="{$cdnUrl}/libraries/moment.js/2.22.0/moment.min.js"></script>
  <script type="text/javascript" src="{$cdnUrl}/libraries/bootstrap-maxlength/1.7.0/bootstrap-maxlength.min.js"></script>
  <script type="text/javascript" src="{$cdnUrl}/libraries/preview-image-jquery/1.0/preview-image.min.js"></script>
  {if count($javascript)}{foreach from=$javascript item=item}
  <script type="text/javascript" src="{$item nofilter}"></script>
  {/foreach}{/if}
  <script type="text/javascript">
    {literal}
    (function($) {
    {/literal}
    {include file="js/modal.tpl"}
    {include file="js/maxlenght.tpl"}
    {if count($jquery)}
      {foreach from=$jquery item=item}
        {$item nofilter}
      {/foreach}
    {/if}
    {literal}
    })(jQuery);
    {/literal}
  </script>
{literal}
<a href="https://github.com/geokrety/geokrety-website" class="github-corner" aria-label="View source on GitHub"><svg width="80" height="80" viewBox="0 0 250 250" style="filter: brightness(85%); fill: #2ab2c2; color:#fff; position: absolute; top: 50px; border: 0; right: 0;" aria-hidden="true"><path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path><path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm"></path><path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body"></path></svg></a><style>.github-corner:hover .octo-arm{animation:octocat-wave 560ms ease-in-out}@keyframes octocat-wave{0%,100%{transform:rotate(0)}20%,60%{transform:rotate(-25deg)}40%,80%{transform:rotate(10deg)}}@media (max-width:500px){.github-corner:hover .octo-arm{animation:none}.github-corner .octo-arm{animation:octocat-wave 560ms ease-in-out}}</style>
{/literal}
</body>
</html>
