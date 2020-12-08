// https://stackoverflow.com/a/43041683/944936
this.on("drop", function (event) {
    console.log(event.dataTransfer);
    let dropzone = this;
    let imageUrl = event.dataTransfer.getData('URL');
    console.log(imageUrl);
    let fileName = imageUrl.split('/').pop();

    // set the effectAllowed for the drag item
    event.dataTransfer.effectAllowed = 'copy';

    function getDataUri(url, callback) {
        let image = new Image();

        image.onload = function () {
            let canvas = document.createElement('canvas');
            canvas.width = this.naturalWidth; // or 'width' if you want a special/scaled size
            canvas.height = this.naturalHeight; // or 'height' if you want a special/scaled size

            canvas.getContext('2d').drawImage(this, 0, 0);

            // Get raw image data
            // callback(canvas.toDataURL('image/png').replace(/^data:image\/(png|jpg);base64,/, ''));

            // ... or get as Data URI
            callback(canvas.toDataURL('image/png'));
        };

        image.setAttribute('crossOrigin', 'anonymous');
        image.src = url;
    }

    function dataURItoBlob(dataURI) {
        let byteString,
            mimestring

        if (dataURI.split(',')[0].indexOf('base64') !== -1) {
            byteString = atob(dataURI.split(',')[1])
        } else {
            byteString = decodeURI(dataURI.split(',')[1])
        }

        mimestring = dataURI.split(',')[0].split(':')[1].split(';')[0]

        let content = new Array();
        for (let i = 0; i < byteString.length; i++) {
            content[i] = byteString.charCodeAt(i)
        }

        return new Blob([new Uint8Array(content)], {
            type: mimestring
        });
    }

    getDataUri(imageUrl, function (dataUri) {
        let blob = dataURItoBlob(dataUri);
        blob.name = fileName;
        dropzone.addFile(blob);
    });
});
