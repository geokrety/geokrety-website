{extends file='base.tpl'}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Developers{/t}</h3>
    </div>
    <div class="panel-body">
        <ul>
            <li><strong>{$contributors['filips']|userlink nofilter}</strong> {t}idea, code and design{/t}</li>
            <li><strong>{$contributors['kumy']|userlink nofilter}</strong> {t escape=no url="https://github.com/geokrety"}hosting, code and <a href="%1">planning GK v2</a>{/t}</li>
            <li><strong>{$contributors['BSLLM']|userlink nofilter}</strong> {t}code, public relation and advertising{/t}</li>
            <li><strong>{$contributors['simor']|userlink nofilter}</strong> {t}visions into code 😉{/t}</li>
        </ul>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Support{/t}</h3>
    </div>
    <div class="panel-body">
        <ul>
            <li><strong>{$contributors['Thathanka']|userlink nofilter}</strong> | <strong>{$contributors['Quinto']|userlink nofilter}</strong> :: {t}GK logo (the mole, different versions){/t}</li>
            <li><strong>gosia</strong> {t}MySQL support, sandwiches and more 😃{/t}</li>
            <li><strong>{$contributors['moose']|userlink nofilter}</strong> {t}GK maps{/t}</li>
        </ul>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Help{/t}</h3>
    </div>
    <div class="panel-body">
        <ul>
            <li><strong>sp2ong</strong> {t}betatesting, idea, public relation and advertising 😃{/t}</li>
            <li><strong>shchenka</strong> {t}betatesting, language support{/t}</li>
            <li><strong>ZYR, Lion &amp; Aquaria</strong> {t}betatesting{/t}</li>
            <li><strong>angelo</strong> {t}programming support{/t}</li>
            <li><strong>Yergo</strong> {t}coordinates parser{/t}</li>
            <li><strong>{$contributors['YvesProvence']|userlink nofilter}</strong> {t}public relation and advertising{/t}</li>
        </ul>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Translations{/t}</h3>
    </div>
    <div class="panel-body">
        <!-- country alphabetical order
       - crowdin contributors (need rights) https://crowdin.com/project/geokrety/settings#reports-top-members
       -->
        <ul>
            <li><strong>Albanian</strong> Hendri Saputra</li>
            <li><strong>Česky</strong> Pavel Kumpán, Ladislav Boháč, Matěj Volf, Ondra Kozel, Juraj Motuz, Pavel Sváda</li><!-- Czech - Tchèque -->
            <li><strong>Catalan</strong> SastRe.O</li>
            <li><strong>Dansk</strong> Niels Langkilde / oz9els</li>
            <li><strong>Deutsch</strong> SigmaZero, Grimpel, Schrottie, {$contributors['Lineflyer']|userlink nofilter}, Rabenkind22</li>
            <li><strong>Eesti</strong> BeautyAndBeast</li>
            <li><strong>English</strong> {$contributors['filips']|userlink nofilter}, {$contributors['kumy']|userlink nofilter}, shchenka</li>
            <li><strong>Español</strong> Zugzwangy, todoporhallar, Xavi Rangel, Iori Yagami</li>
            <li><strong>Finnish</strong> abelard90</li>
            <li><strong>Français</strong> Arnaud Hubert, polaris45, Nam, Daimoneu, {$contributors['BSLLM']|userlink nofilter}, synergy14, Yves Pratter, {$contributors['kumy']|userlink nofilter}</li>
            <li><strong>Indonesian</strong> Saryulis, Hendri Saputra, saifulrahmad, Kartika Rizky, Syaukani, raviyanda</li>
            <li><strong>Italian</strong> Daimoneu, Olivier Renard</li>
            <li><strong>Latviešu</strong> mediamasterLV</li>
            <li><strong>Magyar</strong> M Ernő, Hoffmann Zsolt</li>
            <li><strong>Nederlands</strong> Team Engelenburg, {$contributors['harrieklomp']|userlink nofilter}</li><!-- Dutch -->
            <li><strong>Polski</strong> {$contributors['filips']|userlink nofilter}, shchenka, brasiapl, Jakub Fabijan (Felidae), Piotr Juzwiak</li><!-- Polish -->
            <li><strong>Português</strong> Rui Alberto Almeida, Carlos</li>
            <li><strong>Русский</strong> Максим Милаков, Aleksandr Kostin, Сергей Штейнмиллер</li><!-- Russian -->
            <li><strong>Română</strong> Schiopu Claudiu</li>
            <li><strong>Suomi</strong> Ilpo Kantonen</li>
            <li><strong>Svenska</strong> fredrik, Jonas aka hjontemyra, Henrik Mattsson-Mårn </li><!-- Swedish -->
            <li><strong>Turkish</strong> samet pekel, galadriell, Semra</li>
            <li><strong>{t}Global reviewers{/t}</strong> {$contributors['Lineflyer']|userlink nofilter}, {$contributors['kumy']|userlink nofilter}, {$contributors['filips']|userlink nofilter}, Google translator ;)</li>
        </ul>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Credits{/t}</h3>
    </div>
    <div class="panel-body">
        <div class="dcreds">
        {foreach from=$app_credits item=credit}
            {include file="elements/credit.tpl" credit=$credit}
        {/foreach}
        </div>
    </div>
</div>
{/block}
