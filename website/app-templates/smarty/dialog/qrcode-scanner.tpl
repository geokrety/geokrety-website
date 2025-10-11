<div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="qrScannerModalLabel">
                    {t}Scan QR Code{/t}
                    <span class="badge" id="qr-scan-count" style="margin-left: 10px;">0</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert qr-status" role="alert" style="display: none;"></div>

                <div id="qr-scanned-list" class="panel panel-success" style="display: none; margin-bottom: 15px;">
                    <div class="panel-heading">
                        <h3 class="panel-title">{t}Scanned Codes{/t}</h3>
                    </div>
                    <ul class="list-group" id="qr-codes-list"></ul>
                </div>

                <div id="qr-reader" style="width: 100%;"></div>
                <p class="help-block text-center" id="qr-help-text" style="margin-top: 15px;">
                    {t}Point your camera at a GeoKrety QR code{/t}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="qr-finish-button" style="display: none;">{t}Finish Scanning{/t}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{t}Cancel{/t}</button>
            </div>
        </div>
    </div>
</div>
