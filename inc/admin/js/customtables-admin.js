(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     *
     * The file is enqueued from inc/admin/class-admin.php.
     */


    /*
        function editorFromTextArea(textarea, extensions) {
            let view = new EditorView({doc: textarea.value, extensions})
            textarea.parentNode.insertBefore(view.dom, textarea)
            textarea.style.display = "none"
            if (textarea.form) textarea.form.addEventListener("submit", () => {
                textarea.value = view.state.doc.toString()
            })
            return view
        }
    */

    $(document).ready(function () {

        $('[data-toggle="tab"]').click(function () {
            let tabs = jQuery(this).attr('data-tabs');
            let tab = jQuery(this).attr("data-tab");
            $(tabs).find(".gtab").removeClass("active");
            $('[data-toggle="tab"]').removeClass("nav-tab-active");
            $(tabs).find(tab).addClass("active");
            jQuery(this).addClass("nav-tab-active");
        });


        if (document.getElementById('layoutcode')) {
            let wpObject;

            //In reverse order because we need editor 0 to be active
            wpObject = wp.codeEditor.initialize($('#layoutjs'), cm_settings_layoutjs);
            addCMEvent(wpObject);
            codemirror_editors[3] = wpObject;

            wpObject = wp.codeEditor.initialize($('#layoutcss'), cm_settings_layoutcss);
            addCMEvent(wpObject);
            codemirror_editors[2] = wpObject;

            wpObject = wp.codeEditor.initialize($('#layoutmobile'), cm_settings_layoutmobile);
            addCMEvent(wpObject);
            codemirror_editors[1] = wpObject;

            wpObject = wp.codeEditor.initialize($('#layoutcode'), cm_settings_layoutcode);
            addCMEvent(wpObject);
            codemirror_editors[0] = wpObject;
            wpObject.codemirror.setOption("mode", 'text/html');

            loadTagParams("layouttype", "textareatabid", "WordPress");
            loadTypes_silent("WordPress");

            languages = [];//' . $languages . '];
            loadFields("table", "fieldWizardBox", "WordPress");
            loadLayout(6);
            adjustEditorHeight();
        }
    });

})(jQuery);

function addCMEvent(wpObject) {
    let cm = wpObject.codemirror;

    cm.on('dblclick', function (e) {
        let cr = e.getCursor();
        let lineNumber = cr.line;
        let lineString = e.doc.getLine(lineNumber) ;//state.doc.cm.line(lineNumber).text;
        let mousePos = cm.cursorCoords(cr, "window");
        doExtraCodeMirrorEvent(cr.ch,lineString,lineNumber,mousePos);
    }, true);
}

function CustomTablesAdminLayoutsTabClicked(index, id) {

    setTimeout(function () {
        codemirror_active_index = index;
        codemirror_active_areatext_id = id;
        let cm = codemirror_editors[index];

        if(index === 0 || index ===1)
            cm.codemirror.setOption("mode", 'text/html');
        else if(index === 2)
            cm.codemirror.setOption("mode", 'css');
        else if(index === 3)
            cm.codemirror.setOption("mode", 'javascript');

        cm.codemirror.refresh();
        adjustEditorHeight();
    }, 100);
    return false;
}

function readmoreOpenClose(itemid) {
    var obj = document.getElementById(itemid);
    var c = obj.className;
    if (c.indexOf("ct_readmoreOpen") != -1)
        c = c.replace("ct_readmoreOpen", "ct_readmoreClose");
    else if (c.indexOf("ct_readmoreClosed") != -1)
        c = c.replace("ct_readmoreClosed", "ct_readmoreOpen");
    else if (c.indexOf("ct_readmoreClose") != -1)
        c = c.replace("ct_readmoreClose", "ct_readmoreOpen");

    obj.className = c;
}

function showFieldTagModalForm() {
    current_table_id = document.getElementById("table").value;
    showModalFieldTagsList();
    activateTabsWordPress('layouteditor_fields');
}
