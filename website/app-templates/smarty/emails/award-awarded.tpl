{extends file='emails/base.tpl'}

{block name=title}{t}You have received an Award{/t} 🏆{/block}
{block name=preview}{t}New Award received{/t}{/block}

{block name=content}
<p class="text-justify">{t}We're proud to inform that you have received a new Award badge.{/t}</p>
<div class="s-3"></div>
<div class="text-center">
  {$award|award nofilter}
</div>
<div class="s-3"></div>
<p class="text-justify">{t}Please look at this new pretty badge on your GeoKrety profile.{/t}</p>
<div class="text-center">
  <a class="btn btn-success btn-lg" href="{'user_details'|alias:$award->holder->id:null:'#users-awards'}">{t}Check my profile{/t}</a>
</div>
{/block}
{*It is our pleasure to inform, that you are among*}
{*top $top_ile droppers in $rok (with $count moves, rank #$rank)*}
{*please look at this new pretty badge at your GK profile:*}
{*https://geokrety.org/mypage.php*}

{*Full list of $rok top \"droppers\":*}
{*https://geokrety.org/statystyczka-lata.php?rok=$rok*}

{*thanks!*}

{*Your GeoKrety Team :)*}
