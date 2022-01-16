let data = [
    { 'name': '{t}GeoKrety{/t}', 'url': '{'search_by_geokret'|alias:'geokret=%REPLACE%'}' },
    { 'name': '{t}Users{/t}', 'url': '{'search_by_user'|alias:'username=%REPLACE%'}' },
    { 'name': '{t}Waypoints{/t}', 'url': '{'search_by_waypoint'|alias:'waypoint=%REPLACE%'}' },
]

let $form_advanced_search = $('#formSearchAdvanced');
let $input_advanced_search = $('#inputSearchAdvanced');
let $button_advanced_search_type = $('#buttonSearchAdvancedType');

$input_advanced_search.typeahead({
    minLength: 1,
    showHintOnFocus: 'all',
    autoSelect: true,
    highlighter: function (item) {
        return item + ': ' + this.query;
    },
    source: function (text, callback) {
        return callback(data);
    },
    displayText: function (item) {
        return item.name;
    },
    sorter: function (items) {
        if (data.find(o => o.name === $button_advanced_search_type.html())) {
            return items.sort(function (a, b) {
                if (a.name === $button_advanced_search_type.html()) {
                    return -1;
                }
                return 1;
            })
        }
        return items.sort();
    },
    matcher: function (item) {
        return true;
    },
    updater: function (item) {
        $form_advanced_search.attr('action', item.url);
        $button_advanced_search_type.html(item.name);
        return this.query;
    },
    afterSelect: function () {
        if (this.query.trim() !== '') {
            $form_advanced_search.submit();
        }
    },
});

$form_advanced_search.submit(function( event ) {
    event.preventDefault();
    let current = $input_advanced_search.val().trim();
    if (current === '') {
        return;
    }
    let url = $form_advanced_search.attr('action').replace('%REPLACE%', encodeURIComponent(current));
    $form_advanced_search.attr('action', url);
    window.open(url, "_self");
});
