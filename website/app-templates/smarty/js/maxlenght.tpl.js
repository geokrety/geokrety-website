$('.maxl[maxlength]').maxlength({
    warningClass: "label label-danger",
    limitReachedClass: "label label-success",
});
$('.tcmaxl[maxlength]').maxlength({
    warningClass: "label label-danger",
    limitReachedClass: "label label-success",
    customMaxAttribute: "tcminlength",
});
