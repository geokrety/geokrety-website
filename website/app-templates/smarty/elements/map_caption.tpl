
<div class="panel-group" id="mapCaptionAccordion" role="tablist" aria-multiselectable="true">

    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingThree">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#mapCaptionAccordion"
                   href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    ⏲️ {t}Last moved date{/t}
                </a>
            </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse in" role="tabpanel"
             aria-labelledby="headingThree" data-sort="days">
            <div class="panel-body map-caption">

                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-2-1"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=10}%1 days{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-2-2"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=90}%1 days{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-2-3"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=180}%1 days{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-2-4"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=365}%1 days{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-2-5"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=730}%1 days{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-2-6"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>>= {t count=730}%1 days{/t}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#mapCaptionAccordion" href="#collapseOne"
                   aria-expanded="true" aria-controls="collapseOne">
                    📏 {t}Total distance{/t}
                </a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse" role="tabpanel"
             aria-labelledby="headingOne" data-sort="distance">
            <div class="panel-body map-caption">

                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-1"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {'500'|distance}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-2"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {'1000'|distance}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-3"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {'1500'|distance}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-4"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {'2000'|distance}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-5"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>> {'2000'|distance}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingTwo">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#mapCaptionAccordion"
                   href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <img src="{GK_CDN_IMAGES_URL}/log-icons/2caches.png"
                         title="{t}Caches visited count{/t}"/>
                    {t}Total visited caches{/t}
                </a>
            </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo" data-sort="caches">
            <div class="panel-body map-caption">

                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-1"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=10}%1 caches{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-2"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=50}%1 caches{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-3"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=100}%1 caches{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-4"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>< {t count=200}%1 caches{/t}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">
                        <div class="caption-gradient gradient-1-5"></div>
                    </div>
                    <div class="col-xs-9">
                        <div>>= {t count=200}%1 caches{/t}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
