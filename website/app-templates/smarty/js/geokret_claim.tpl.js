$('body').on('submit', '#formClaim', function(event) {
    var spinner = new Spinner({
        lines: 15,
        length: 40,
        width: 11,
        radius: 32,
        scale: 1.55,
        color: "#2ab2c2",
    }).spin(document.getElementById('spinner-center'));
});
