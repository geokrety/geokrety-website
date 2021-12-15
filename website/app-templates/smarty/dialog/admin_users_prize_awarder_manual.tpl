{block name=content}
<div class="modal-header alert-success">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t username=$user->username}Award %1 with:{/t}</h4>
</div>

<form name="UserAwardPrizeForm" action="{'admin_users_prize_awarder_manual'|alias:sprintf('@userid=%d,@award_id=%d', $user->id, $award->id)}" method="post" class="form-horizontal">
    <div class="modal-body">
        {if $award->valid()}
            {$award|award:false nofilter}
        {else}
            <div class="form-group">
                <label for="award-selector" class="col-sm-2 control-label">{t}Award{/t}</label>
                <div class="col-sm-5">
                    <select id="award-selector" name="award_id">
                        <option value="" data-url=""></option>
                    {foreach $awards as $award}
                        <option value="{$award->id}" data-url="{$award->url}">{$award|award:false nofilter}</option>
                    {/foreach}
                    </select>
                </div>
                <div class="col-sm-5">
                    <img id="award-preview" alt="" src="">
                </div>
            </div>

            <div class="form-group">
                <label for="award-comment" class="col-sm-2 control-label">Comment</label>
                <div class="col-sm-10">
                    <input type="text" id="award-comment" name="comment" required>
                </div>
            </div>
        {/if}
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-success" id="award-button">{t}Award{/t}</button>
    </div>
</form>
{/block}
