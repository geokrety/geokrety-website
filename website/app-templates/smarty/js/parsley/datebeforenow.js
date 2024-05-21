/*global moment*/

window.Parsley.addValidator("datebeforenow", {
    validateString: (value, format) => {
        if (! value) {
            return true;
        }
        var date = moment(value, format, true);
        if (! date.isValid()) {
            return false;
        }
        return date.isSameOrBefore(moment());
    },
    messages: {
        en: "The date cannot be in the future.",
        fr: "La date ne peut pas etre dans le futur."
    },
    priority: 256,
});
