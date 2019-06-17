/**
 *  Some common JS functions
 */
$('[data-toggle="tooltip"]').tooltip();

// Query nested object value by key
Object.byString = function(o, s) {
    s = s.replace(/\[(\w+)\]/g, '.$1'); // convert indexes to properties
    s = s.replace(/^\./, '');           // strip a leading dot
    var a = s.split('.');
    for (var i = 0, n = a.length; i < n; ++i) {
        var k = a[i];
        if (o[k]) {
            o = o[k];
        } else {
            return;
        }
    }
    return o;
}
