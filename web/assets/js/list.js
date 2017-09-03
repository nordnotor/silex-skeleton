function changeUrl(data, clearUrl) {
// alert(data)
    var u = new Url;
    clearUrl = clearUrl || false;

    if (clearUrl) {
        u.clearQuery();
    }

    $.extend(u.query, data);
    window.location.href = u;
}

function search(limit) {

    var u = new Url;
    var data = {'limit': limit};
    var inputVal = $('#search-input').val();

    u.clearQuery();

    if (inputVal.length > 0) {
        data.search = inputVal;
    }
    $.extend(u.query, data);

    window.location.href = u;
}


/* For filter (search by field) */
function searchField(field, inputVal) {

    var u = new Url;
    var data = {};

    // u.clearQuery();

    if (inputVal.length > 0 && inputVal != 'null') {

        data[field] = inputVal;
        // data[field] = inputVal.trim();
        $.extend(u.query, data);

    } else {
        delete u.query[field]
    }

    delete u.query['current_page']

    window.location.href = u;
}

// для select multiple
function searchMultiSelect(field, select) {

    var values = [];
    for (var i = 0; i < select.selectedOptions.length; i++) {
        values.push(select.selectedOptions[i].value.trim());
    }

    searchField(field, values)
}