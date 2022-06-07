{function common alias=''}
    language: {
        url: '{$datatable_language_url}',
    },
    lengthMenu: [10, 25, 50],
    serverSide: true,
    processing: true,
    ajax: {
        "url": '{$alias|alias}',
        "dataSrc": function(json) {
            var data = []
            var i = 0
            $(json.data).find('tr').each(function() {
                data.push([]);
                $(this).find('td').each(function() {
                    data[i].push($(this).html());
                })
                i++;
            })
            return data
        }
    },
{/function}
