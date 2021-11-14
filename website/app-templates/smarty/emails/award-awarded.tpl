{extends file='emails/base.tpl'}

{block name=title}{t}You have received an Award{/t} 🏆{/block}
{block name=preview}{t}New Award received{/t}{/block}

{block name=content}
<p class="text-justify">{t}We're proud to inform you that you have received a new Award badge.{/t}</p>
<div class="s-3"></div>
<div class="text-center">
  {$award|award nofilter}
</div>
<div class="text-center">
  {$award->description}
</div>
<div class="s-3"></div>
<p class="text-justify">{t}Please look at this new pretty badge on your GeoKrety profile.{/t}</p>
<div class="text-center">
  <a class="btn btn-success btn-lg" href="{'user_details'|alias:sprintf('userid=%d', $award->holder->id):null:'#users-awards'}">{t}Check my profile{/t}</a>
</div>
{/block}
