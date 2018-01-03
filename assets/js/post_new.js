jQuery(document).ready(function(){
    var query = window.location.search.substring(1);
    var qs = parse_query_string(query);
    
    for (var key in qs) {
        if (
            qs.hasOwnProperty(key) && 
            jQuery('#'+key).length == 1
        ) {
            jQuery('#'+key).val(qs[key]);
        }
    }
});

function parse_query_string(query) {
    var vars = query.split("&");
    var query_string = {};
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = decodeURIComponent(pair[1]);
            // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
            var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
            query_string[pair[0]] = arr;
            // If third or later entry with this name
        } else {
            query_string[pair[0]].push(decodeURIComponent(pair[1]));
        }
    }
    return query_string;
}