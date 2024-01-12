<div class="panel panel-default">
    <div class="panel-heading">
         <h3 class="panel-title">{t}Create News{/t}</h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
            <div class="form-group">
                <label for="inputTitle" class="col-sm-2 control-label">{t}Title{/t}</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control maxl" id="inputTitle" name="title" placeholder="{t}Title{/t}" maxlength="{GK_NEWS_TITLE_MAX_LENGTH}" required value="{if isset($news)}{$news->title}{/if}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputContent" class="col-sm-2 control-label">{t}Content{/t}</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="inputContent" name="content" placeholder="{t}Write the News here{/t}">{if isset($news->id)}{$news->content nofilter}{else}{/if}</textarea>
                </div>
            </div>


            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {call csrf}
                    <button type="submit" class="btn" id="buttonSave" name="save">{t}Save{/t}</button>
                    <button type="submit" class="btn btn-primary" id="buttonSaveDraft" name="saveDraft">{t}Save as Draft{/t}</button>
                </div>
            </div>

        </form>
    </div>
</div>
