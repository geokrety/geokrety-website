{extends file='base.tpl'}

{block name=title}{t}Statpic background chooser{/t}{/block}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Select your preferred statpic background{/t}</h3>
    </div>
    <div class="panel-body">

        <form class="form-horizontal" method="post">

            <div class="statpic-chooser">
                {for $statpic=1 to $statpic_template_count}
                <div class="radio radio-inline">
                    <label>
                        <input type="radio" name="statpic" value="{$statpic}" {if $currentUser->statpic_template === $statpic} checked{/if}>
                        {$statpic|statpictemplate nofilter}
                    </label>
                </div>
                {/for}
            </div>

            <div class="modal-footer">
                <div class="col-sm-offset-2 col-sm-10">
                    {call csrf}
                    <button id="bannerTemplateSubmitButton" type="submit" class="btn btn-primary">{t}Save{/t}</button>
                </div>
            </div>

        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Your current statpic{/t}</h3>
    </div>
    <div class="panel-body">
        {$currentUser|userstatpic nofilter}
    </div>
</div>
{/block}
