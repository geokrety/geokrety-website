<div class="panel panel-default">
    <div class="panel-heading">
         <h3 class="panel-title">{t}Generate multiple GeoKrety labels{/t}</h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" method="POST" action="{'geokrety_labels_pdf'|alias}" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>

            <div class="form-group">
                <div class="col-sm-offset-1 col-sm-11">
                    <ul>
                        <li>{t num=GK_LABELS_GENERATE_MAX}The current number of labels to be created at once: %1.{/t}</li>
                        <li>{t}Only your own GeoKrety of the ones you already touched can be used here.{/t}</li>
                        <li>{t}Label preferred template is used if defined.{/t}</li>
                        <li>{t}The GeoKret mission will be used as is. Text overflow are generally removed.{/t}</li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8">
                    <div class="form-group">
                        <label for="nr" class="col-sm-2 control-label">{t}Tracking Code{/t}</label>
                        <div class="col-sm-10">

                            <div class="input-group">
                                <input type="text" name="tracking_code" id="nr" value="" minlength="{GK_SITE_TRACKING_CODE_LENGTH}" required class="form-control" placeholder="eg. DQ9H4B" aria-describedby="helpBlockTrackingCode" data-parsley-trigger="input focusout" data-parsley-validation-threshold="{GK_SITE_TRACKING_CODE_LENGTH -1}" data-parsley-debounce="500" data-parsley-remote data-parsley-remote-validator="checkNr" data-parsley-errors-messages-disabled style="text-transform:uppercase" data-parsley-group="trackingCode" data-parsley-remote-options='{ "type": "POST" }' />
                                <span class="input-group-btn">
                                    {if $f3->get('SESSION.CURRENT_USER')}
                                        <button class="btn btn-default" type="button" id="nrInventorySelectButton" title="{t}Select GeoKrety from inventory{/t}" data-toggle="modal" data-target="#modal" data-type="select-from-inventory">{fa icon="briefcase"}</button>
                                    {/if}
                                    <button class="btn btn-default" type="button" id="nrSearchButton" title="{t}Verify Tracking Code{/t}">{fa icon="search"}</button>
                                </span>
                            </div>
                            <p id="helpBlockTrackingCode" class="help-block tooltip_large" data-toggle="tooltip" title="<img src='{GK_CDN_IMAGES_URL}/labels/screenshots/label-screenshot.svg' style='width:100%' />" data-html="true">{t escape=no count={GK_SITE_TRACKING_CODE_LENGTH}}%1 characters from <em>GeoKret label</em>. <u>Do not use the code starting with 'GK' here</u>{/t}</p>
                        </div>
                    </div>
                </div>
                <ul class="col-sm-4 alert alert-success hidden list-unstyled" id="nrResult"></ul>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {call csrf}
                    <button type="submit" class="btn btn-primary" id="generateAsPdf" name="generateAsPdf">{t}Generate as .pdf{/t}</button>
                </div>
            </div>

        </form>
    </div>
</div>
