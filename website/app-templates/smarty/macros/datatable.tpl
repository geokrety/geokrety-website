{function common alias=''}
    language: {
        url: '{$datatable_language_url}',
    },
    lengthMenu: [10, 25, 50, 100, 500, 1000],
    serverSide: true,
    processing: true,
    ajax: {
        "url": '{$alias|alias}',
        "dataSrc": function(json) {
            // Convert html table rows to json object array
            // (loosing td attributes and styles)
            var data = [];
            var i = 0;
            $(json.data).find('tr').each(function() {
                data.push({ "DT_RowClass": $(this).attr('class') });
                var j = 0;
                $(this).find('td').each(function() {
                    data[i][j++] = $(this).html();
                })
                i++;
            })
            return data
        }
    },
    "drawCallback" : function(settings) {
        bind_gk_avatars_buttons()
    },
{/function}
