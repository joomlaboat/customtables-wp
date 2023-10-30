let customtables_tables = [];
let customtables_layouts = [];
let customtables_prerenderedContent = [];

!function () {
    "use strict";
    var e, t =
        {
            741: function () {
                var e = window.wp.blocks,
                    /*
                    t = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"customtables/dynamic-block","version":"0.1.0","title":"Example: Dynamic Block (ESNext)","category":"text","icon":"universal-access-alt",' +
                        '"attributes":{"message":{"type":"string","default":"Hello from a dynamic block!"}},"example":{"attributes":{"message":"CustomTables Block"}},"supports":{"html":false},"textdomain":"dynamic-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

                     */

                    t = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"customtables/dynamic-block","version":"0.1.0","title":"Example: Dynamic Block (ESNext)","category":"text","icon":"universal-access-alt",' +
                        '"attributes":{"message":{}},"example":{"attributes":{"message":"CustomTables Block"}},"supports":{"html":false},"textdomain":"dynamic-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

                function n() {
                    return n = Object.assign || function (e) {
                        for (var t = 1; t < arguments.length; t++) {
                            var n = arguments[t];
                            for (var r in n) Object.prototype.hasOwnProperty.call(n, r) && (e[r] = n[r])
                        }
                        return e
                    }, n.apply(this, arguments)
                }

                //Load list of tables, layouts and render the block and the side panel
                CustomTablesLoadTables();
                CustomTablesLoadLayouts();

                var r = window.wp.element, o = window.wp.blockEditor;
                const {name: i} = t;
                CustomTablesRenderBlock(e, i);

            }
        }, n = {};

    function r(e) {
        var o = n[e];
        if (void 0 !== o) return o.exports;
        var i = n[e] = {exports: {}};
        return t[e](i, i.exports, r), i.exports
    }

    r.m = t, e = [], r.O = function (t, n, o, i) {
        if (!n) {
            var a = 1 / 0;
            for (u = 0; u < e.length; u++) {
                n = e[u][0], o = e[u][1], i = e[u][2];
                for (var s = !0, c = 0; c < n.length; c++) (!1 & i || a >= i) && Object.keys(r.O).every((function (e) {
                    return r.O[e](n[c])
                })) ? n.splice(c--, 1) : (s = !1, i < a && (a = i));
                if (s) {
                    e.splice(u--, 1);
                    var l = o();
                    void 0 !== l && (t = l)
                }
            }
            return t
        }
        i = i || 0;
        for (var u = e.length; u > 0 && e[u - 1][2] > i; u--) e[u] = e[u - 1];
        e[u] = [n, o, i]
    }, r.o = function (e, t) {
        return Object.prototype.hasOwnProperty.call(e, t)
    }, function () {
        var e = {826: 0, 431: 0};
        r.O.j = function (t) {
            return 0 === e[t]
        };
        var t = function (t, n) {
            var o, i, a = n[0], s = n[1], c = n[2], l = 0;
            if (a.some((function (t) {
                return 0 !== e[t]
            }))) {
                for (o in s) r.o(s, o) && (r.m[o] = s[o]);
                if (c) var u = c(r)
            }
            for (t && t(n); l < a.length; l++) i = a[l], r.o(e, i) && e[i] && e[i][0](), e[i] = 0;
            return r.O(u)
        }, n = self.webpackChunkdynamic_block = self.webpackChunkdynamic_block || [];
        n.forEach(t.bind(null, 0)), n.push = t.bind(null, n.push.bind(n))
    }();
    var o = r.O(void 0, [431], (function () {
        return r(741)
    }));
    o = r.O(o)
}();

function CustomTablesLoadTables() {
    //Load list of tables
    let parts = location.href.split("wp-admin/");
    let url = parts[0] + 'wp-admin/admin.php?page=customtables-api-tables';

    fetch(url, {method: 'GET', mode: 'no-cors', credentials: 'same-origin'}).then(function (response) {

        if (response.ok) {
            response.json().then(function (json) {

                customtables_tables = wizardFields = Array.from(json);

            });
        } else {
            console.log('CustomTables - Block widget: Network request for products.json failed with response ' + response.status + ': ' + response.statusText);
        }

    }).catch(function (err) {
        console.log(url, err);
        console.log('CustomTables - Block widget: Cannot load list of Tables.', err);
    });
}

function CustomTablesLoadLayouts() {
    //Load list of tables
    let parts = location.href.split("wp-admin/");
    let url = parts[0] + 'wp-admin/admin.php?page=customtables-api-layouts';

    fetch(url, {method: 'GET', mode: 'no-cors', credentials: 'same-origin'}).then(function (response) {

        if (response.ok) {
            response.json().then(function (json) {

                customtables_layouts = wizardFields = Array.from(json);

            });
        } else {
            console.log('CustomTables - Block widget: Network request for products.json failed with response ' + response.status + ': ' + response.statusText);
        }

    }).catch(function (err) {
        console.log(url, err);
        console.log('CustomTables - Block widget: Cannot load list of Layouts.', err);
    });
}

function CustomTablesLoadPreview(newAttributes,props) {
    console.log("CustomTablesLoadPreview");
    console.log(typeof props);

    //Load list of tables
    let parts = location.href.split("wp-admin/");
    console.log(JSON.stringify(props.attributes));
    let url = parts[0] + 'wp-admin/admin.php?page=customtables-api-preview&attributes=' + btoa(JSON.stringify(newAttributes));
    console.log(JSON.stringify(newAttributes));

    fetch(url, {method: 'GET', mode: 'no-cors', credentials: 'same-origin'}).then(function (response) {

        if (response.ok) {
            response.text().then(function (text) {
                //newAttributes.loading = 0;
                let blockId = cyrb53(JSON.stringify(newAttributes));
                console.log("blockId:"+blockId);
                customtables_prerenderedContent[blockId] = text;
                console.log("updated");
                console.log(typeof props);

                setTimeout(function() {
                    props.setAttributes({loading: 1});
                    }, 200);

            });

        } else {
            console.log('CustomTables - Block widget: Network request for products.json failed with response ' + response.status + ': ' + response.statusText);
        }

    }).catch(function (err) {
        console.log(url, err);
        console.log('CustomTables - Block widget: Cannot load list of Layouts.', err);
    });
}

function CustomTablesRenderBlock(e, i) {

    (0, e.registerBlockType)(i, {

        edit: function (props) {
            customtables_setAttribute = props.setAttribute;
            console.log("edit render");

            //With Control Panel
            var blockProps = wp.blockEditor.useBlockProps();

            props.attributes.loading=0;
            let blockId = cyrb53(JSON.stringify(props.attributes));

            function updateArray(newValue) {
                props.setAttributes({connections: newValue});
            };


            function addToArray(connection) {
                const newArray = [...props.attributes.connections, connection];
                updateArray(newArray);
            };

            function removeFromArray(index) {
                const newArray = props.attributes.connections.filter((_, i) => i !== index);
                updateArray(newArray);
            };


            function addConnection(i, place) {
                let title = place.name;
                if (place.lang) title += ' (' + place.lang + ')';

                return el(
                    PanelBody,
                    {
                        title: title,
                        initialOpen: false
                    },
                    el('div', {className: 'grw-builder-option'},
                        el(
                            'img',
                            {
                                src: place.photo,
                                alt: place.name,
                                className: 'grw-connect-photo'
                            }
                        ),
                        el(
                            'a',
                            {
                                className: 'grw-connect-photo-change',
                                href: '#',
                            },
                            'Change'
                        ),
                        el(
                            'a',
                            {
                                className: 'grw-connect-photo-default',
                                href: '#',
                            },
                            'Default'
                        ),
                        el(
                            TextControl,
                            {
                                type: 'hidden',
                                name: 'photo',
                                className: 'grw-connect-photo-hidden',
                                value: place.id,
                                tabindex: 2
                            }
                        )
                    ),
                    el('div', {className: 'grw-builder-option'},
                        el(
                            'input',
                            {
                                name: 'name',
                                value: place.name,
                                type: 'text'
                            }
                        ),
                    ),
                    el('div', {className: 'grw-builder-option'},
                        LangControl('Show all connected languages', place.lang)
                    ),
                    el('div', {className: 'grw-builder-option'},
                        el(
                            'button',
                            {
                                className: 'grw-connect-reconnect',
                                onClick: function () {

                                }
                            },
                            'Reconnect'
                        )
                    ),
                    el('div', {className: 'grw-builder-option'},
                        el(
                            'button',
                            {
                                className: 'grw-connect-delete',
                                onClick: function () {
                                    removeFromArray(i);
                                }
                            },
                            'Delete connection'
                        )
                    ),
                )
            };

            let el = wp.element.createElement;
            let InspectorControls = wp.editor.InspectorControls;
            let PanelBody = wp.components.PanelBody;
            let SelectControl = wp.components.SelectControl;
            let TextControl = wp.components.TextControl;
            let __ = wp.i18n.__;
            //let RawHTML = wp.element.RawHTML;
            var connEls = [];

            function LangControl(def, lang) {
                let opts = [];
                opts.push(el('option', {value: ''}, def));

                return el
                (
                    'select',
                    {
                        name: 'lang',
                        type: 'select',
                        className: '2grw-connect-lang'
                    },
                    opts
                );
            }

            var connectGoogle = function (e) {
            }

            //alert("props.attributes.loading="+props.attributes.loading);
            //alert("props.attributes.loading="+props.attributes.loading);

            let generatedPreview;
            if(customtables_prerenderedContent[blockId] === undefined) {
                CustomTablesLoadPreview(props.attributes,props);
            }
            else {
                generatedPreview = (customtables_prerenderedContent[blockId] !== '' ? customtables_prerenderedContent[blockId] : '<p>CustomTables Block:<br/>Please select a Table and Layout.</p>')
            }

            //delete customtables_prerenderedContent[blockId];

            return el(
                'div',
                blockProps,
                el(
                    InspectorControls,
                    {
                        key: 'inspector'
                    },
                    el(
                        'div',
                        {
                            id: '2grw-builder-option',
                            className: '2grw-builder-options 2grw-block-options'
                        },
                        el(
                            PanelBody,
                            {
                                title: __('Table'),
                                initialOpen: true,
                                className: '2grw-toggle grw-builder-connect 2grw-connect-business'
                            },
                            el(
                                SelectControl,
                                {
                                    id: 'customtables_block_loading',
                                    name: 'customtables_block_loading',
                                    value: props.attributes.table,
                                    options: [{label: 'state 0',value: 0},{label: 'state 1',value: 1}],
                                    onChange: function (newValue) {
                                        console.log("on change called");
                                        props.setAttributes({loading: 0});
                                        CustomTablesLoadPreview(props);
                                    },
                                    style:{visibility: 'hidden'}
                                }
                            ),
                            el(
                                SelectControl,
                                {
                                    id: 'table',
                                    name: 'table',
                                    value: props.attributes.table,
                                    options: customtables_tables,
                                    onChange: function (newValue) {
                                        props.setAttributes({table: newValue});
                                        props.setAttributes({loading: 0});

                                        let newAttributes = props.attributes;
                                        newAttributes.table = newValue;

                                        CustomTablesLoadPreview(newAttributes,props);
                                    }
                                }
                            )
                        ),
                        el(
                            PanelBody,
                            {
                                title: __('Layout'),
                                initialOpen: true
                            },
                            el(
                                SelectControl,
                                {
                                    id: 'layout',
                                    name: 'layout',
                                    value: props.attributes.layout,
                                    options: customtables_layouts,
                                    onChange: function (newValue) {
                                        props.setAttributes({layout: newValue});
                                        props.setAttributes({loading: 0});

                                        let newAttributes = props.attributes;
                                        newAttributes.layout = newValue;

                                        CustomTablesLoadPreview(newAttributes,props);
                                    }
                                }
                            )
                        ),
                        el(
                            PanelBody,
                            {
                                title: __('Filter'),
                                initialOpen: false
                            },
                            el(
                                TextControl,
                                {
                                    label: 'Filter (Where clause)',
                                    value: props.attributes.filter,
                                    onChange: function (newValue) {
                                        props.setAttributes({filter: newValue});
                                        props.setAttributes({loading: 0});

                                        let newAttributes = props.attributes;
                                        newAttributes.filter = newValue;

                                        CustomTablesLoadPreview(newAttributes,props);
                                    }
                                }
                            ),
                        ),
                        el(
                            PanelBody,
                            {
                                title: __('Sorting'),
                                initialOpen: false
                            },
                            el(
                                TextControl,
                                {
                                    label: 'Order By',
                                    value: props.attributes.orderby,
                                    onChange: function (newValue) {
                                        props.setAttributes({orderby: newValue});
                                        props.setAttributes({loading: 0});

                                        let newAttributes = props.attributes;
                                        newAttributes.orderby = newValue;

                                        CustomTablesLoadPreview(newAttributes,props);
                                    }
                                }
                            ),
                            el(
                                TextControl,
                                {
                                    label: 'Direction',
                                    value: props.attributes.order,
                                    onChange: function (newValue) {
                                        props.setAttributes({order: newValue});
                                        props.setAttributes({loading: 0});

                                        let newAttributes = props.attributes;
                                        newAttributes.order = newValue;

                                        CustomTablesLoadPreview(newAttributes,props);
                                    }
                                }
                            )
                        )
                    )
                ),
                el(
                    'div',
                    {
                        id: 'customtables-block-wizard',
                        title: 'CustomTables Block',
                        style: {
                            'display': 'block',
                            'padding': '10px 20px',
                            /*'border-radius': '5px',
                            'background': '#fff'*/
                        }
                    },
                    el(
                        wp.element.RawHTML,
                        null,
                        generatedPreview
                    )
                )
            );
            //End of control panel
        },
        save: props => {
            props.attributes.loading=1;
            let attributes = props.attributes;
            let newAttributes = {
                table: attributes.table,
                layout: attributes.layout,
                filter: attributes.filter,
                orderby: attributes.orderby,
                order: attributes.order,
                loading: 0
            }
                console.log("saved");
            //let blockId = cyrb53(JSON.stringify(props.attributes));
            //alert("1customtables_prerenderedContent.length:"+Object.keys(customtables_prerenderedContent).length);
            //delete customtables_prerenderedContent[blockId];
            //alert("2customtables_prerenderedContent.length:"+Object.keys(customtables_prerenderedContent).length);
            //alert(typeof customtables_setAttribute)

            //if(document.getElementById("customtables_block_loading"))
//                document.getElementById("customtables_block_loading").value = 0;

  //          CustomTablesLoadPreview(newAttributes,props.attributes);



  //          setTimeout(function() {

//                if(document.getElementById("inspector-text-control-1"))
              //      document.getElementById("inspector-text-control-1").value = 1;

            //}, 4000);

            return newAttributes;
        },
    })
}

const cyrb53 = (str, seed = 0) => {
    let h1 = 0xdeadbeef ^ seed, h2 = 0x41c6ce57 ^ seed;
    for(let i = 0, ch; i < str.length; i++) {
        ch = str.charCodeAt(i);
        h1 = Math.imul(h1 ^ ch, 2654435761);
        h2 = Math.imul(h2 ^ ch, 1597334677);
    }
    h1  = Math.imul(h1 ^ (h1 >>> 16), 2246822507);
    h1 ^= Math.imul(h2 ^ (h2 >>> 13), 3266489909);
    h2  = Math.imul(h2 ^ (h2 >>> 16), 2246822507);
    h2 ^= Math.imul(h1 ^ (h1 >>> 13), 3266489909);

    return 4294967296 * (2097151 & h2) + (h1 >>> 0);
};

