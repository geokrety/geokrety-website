{if $pictures.subset}
    {call pagination pg=$pg anchor='gallery'}
    <div class="gallery">
        {foreach from=$pictures.subset item=picture}
            {$picture|picture:true:true:false:true:true nofilter}
        {/foreach}
    </div>
    {call pagination pg=$pg anchor='gallery'}
{else}
    <em>{t}There is no pictures yet.{/t}</em>
{/if}
