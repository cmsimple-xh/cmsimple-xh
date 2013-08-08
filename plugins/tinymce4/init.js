/**
 * @version $Id: init.js 229 2012-07-30 13:31:07Z cmb69 $
 */

function tinyMCE_getTextareasByClass(name) {
    var textareas = document.getElementsByTagName('textarea');
    var pattern = new RegExp('(^|\\s)' + name + '(\\s|$)');
    var res = new Array();
    for (var i = 0, j = 0; i < textareas.length; i++) {
        if (pattern.test(textareas[i].className)) {
            res[j++] = textareas[i];
        }
    }
    return res;
}


function tinyMCE_uniqueId() {
    var id = 'tinyMCE';
    var i = 0;
    while (document.getElementById(id + i) !== null) {i++}
    return id + i;
}

function tinyMCE_instantiateByClasses(classes, config) {
    classes = classes.split('|');
    for (var i = 0; i < classes.length; i++) {
        var textareas = tinyMCE_getTextareasByClass(classes[i]);
        for (var j = 0; j < textareas.length; j++) {
            if (!textareas[j].getAttribute('id')) {
                textareas[j].setAttribute('id', tinyMCE_uniqueId());
            }
            new tinymce.Editor(textareas[j].getAttribute('id'), config).render();
        }
    }
    
}