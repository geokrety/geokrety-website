{extends file='base.tpl'}

{block name=title}{t}Help{/t}{/block}

{block name=content}
{include file=$file}
{/block}

{block name=javascript}
    $('div.panel-heading > a[id].anchor').each(function() {
        $('<a class="headerlink">\u00B6</a>').
        attr('href', '#' + this.id).
        attr('title', '{t}Permalink to this headline{/t}').
        appendTo($(this).parent('div.panel-heading'));
    });
{/block}
