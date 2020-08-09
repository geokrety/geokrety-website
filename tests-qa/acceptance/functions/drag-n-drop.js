// https://stackoverflow.com/a/49581588/944936
var dataTransfer=
                {
                    dropEffect:'',
                    effectAllowed:'all',
                    files:[],
                    items:{},
                    types:[],
                    setData:function(format,data)
                    {
                        this.items[format]=data;
                        this.types.push(format);
                    },
                    getData:function(format)
                    {
                        return this.items[format];
                    },
                    clearData:function(format){}
                };
var emit=function(event,target)
                {
                    var evt=document.createEvent('Event');
                    evt.initEvent(event,true,false);
                    evt.dataTransfer=dataTransfer;
                    target.dispatchEvent(evt);
                };
function getElementByXpath(path) {return document.evaluate(path, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;}

var DragNDrop=function(src,tgt) {
    // src = document.getElementById(src);
    // tgt = document.getElementById(tgt);
    src = getElementByXpath(src);
    tgt = getElementByXpath(tgt);
    dataTransfer.setData('URL', src.getAttribute('src'));
    emit('dragstart',src);
    emit('dragenter',tgt);
    emit('dragover',tgt);
    emit('drop',tgt);
    emit('dragend',src);
    return true;
}

// DragNDrop('userDetailsTypeIcon', 'userAvatar');
