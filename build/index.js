!function () {
    "use strict";
    var e, t =
        {
            741: function () {
                var e = window.wp.blocks,
                    t = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"customtables/dynamic-block","version":"0.1.0","title":"Example: Dynamic Block (ESNext)","category":"text","icon":"universal-access-alt",' +
                        '"attributes":{"message":{"type":"string","default":"Hello from a dynamic block!"}},"example":{"attributes":{"message":"CustomTables Block"}},"supports":{"html":false},"textdomain":"dynamic-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

                function n() {
                    return n = Object.assign || function (e) {
                        for (var t = 1; t < arguments.length; t++) {
                            var n = arguments[t];
                            for (var r in n) Object.prototype.hasOwnProperty.call(n, r) && (e[r] = n[r])
                        }
                        return e
                    }, n.apply(this, arguments)
                }

                var r = window.wp.element, o = window.wp.blockEditor;
                const {name: i} = t;
                (0, e.registerBlockType)(i, {

                    edit: function (props) {
                        //Example
                        /*
                        let {
                            attributes: {message: t},
                            setAttributes: i
                        } = e;
                        return (0, r.createElement)(o.RichText, n({}, (0, o.useBlockProps)(), {
                            tagName: "p",
                            value: t,
                            onChange: e => i({message: e})
                        }))
                        */

                        //With Control Panel

                        var attributes = props.attributes;
                        var blockProps = wp.blockEditor.useBlockProps();


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
                        /*
                                    var connectGoogle = function (e) {
                                        let btn = e.target,
                                            id = btn.parentNode.querySelector('.grw-connect-id input').value,
                                            lang = btn.parentNode.querySelector('.grw-connect-lang').value;

                                        const data = new URLSearchParams();
                                        data.append('id', decodeURIComponent(id));
                                        data.append('lang', lang);
                                        data.append('grw_wpnonce', grwBlockData.nonce);
                                        data.append('action', 'grw_connect_google');
                                        data.append('v', new Date().getTime());

                                        wp.apiFetch({
                                            method: 'POST',
                                            url: ajaxurl,
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                            },
                                            body: data.toString()
                                        })
                                            .then(response => {
                                                addToArray({
                                                    id: response.result.id,
                                                    name: response.result.name,
                                                    photo: response.result.photo,
                                                    lang: lang,
                                                    refresh: true,
                                                    local_img: false,
                                                    platform: 'google'
                                                });
                                                console.log('Response from server:', response);
                                            })
                                            .catch(error => {
                                                console.error('Error during AJAX request:', error);
                                            });
                                    };
                        */
                        //return '123';
                        /*
                                var connEls = [];
                                for (let i = 0; i < attributes.connections.length; i++) {
                                    (function (i, connection) {
                                        connEls.push(addConnection(i, connection));
                                    })(i, attributes.connections[i]);
                                }*/

                        let el = wp.element.createElement;
                        let InspectorControls = wp.editor.InspectorControls;
                        let PanelBody = wp.components.PanelBody;
                        let SelectControl = wp.components.SelectControl;
                        let TextControl = wp.components.TextControl;
                        let __                = wp.i18n.__;


                        //        return el(
                        //              'div',null,
//                'Hello Second Owl.');
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
                                    className: 'grw-connect-lang'
                                },
                                opts
                            );
                        }

                        var connectGoogle = function(e) {}

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
                                        id: 'grw-builder-option',
                                        className: 'grw-builder-options grw-block-options'
                                    },
                                    el(
                                        PanelBody,
                                        {
                                            title: __('Connections'),
                                            initialOpen: true,
                                            className: 'grw-toggle grw-builder-connect grw-connect-business'
                                        },
                                        el(
                                            'button',
                                            {
                                                className: 'grw-builder-connect grw-connect-google',
                                                style: {width: '100%'},
                                                onClick: function () {
                                                    let wizardEl = jQuery('#grw-connect-wizard');
                                                    wizardEl.dialog({modal: true, width: '50%', maxWidth: '600px'});
                                                    wizardEl[0].querySelector('.grw-connect-btn').onclick = connectGoogle;
                                                }
                                            },
                                            'Select Table'
                                        ),
                                        connEls
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
                                                id: 'view_mode',
                                                name: 'view_mode',
                                                value: props.attributes.view_mode,
                                                options: [
                                                    {label: 'Simple Catalog', value: 'slider'},
                                                    {label: 'Edit Form', value: 'list'}
                                                ],
                                                onChange: function (newValue) {
                                                    props.setAttributes({view_mode: newValue});
                                                }
                                            }
                                        )
                                    ),
                                    el(
                                        PanelBody,
                                        {
                                            title: __('Not so Common Options'),
                                            initialOpen: false
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Pagination',
                                                value: props.attributes.pagination,
                                                onChange: function (newValue) {
                                                    props.setAttributes({pagination: newValue});
                                                }
                                            }
                                        ),
                                        el(
                                            TextControl,
                                            {
                                                label: 'Maximum characters before \'read more\' link',
                                                value: props.attributes.text_size,
                                                onChange: function (newValue) {
                                                    props.setAttributes({text_size: newValue});
                                                }
                                            }
                                        )
                                    )
                                )
                            ),
                            el(
                                'div',
                                {
                                    id: 'grw-connect-wizard',
                                    title: 'Easy steps to connect Google Reviews',
                                    style: {
                                        'display': 'block',
                                        'padding': '10px 20px',
                                        'border-radius': '5px',
                                        'background': '#fff'
                                    }
                                },
                                el(
                                    'p',
                                    null,
                                    el('span', null, '1'),
                                    ' Custom Tables Block (',
                                    el('u', {className: 'grw-wiz-arr'}, 'Enter a location'),
                                    ') and copy your ',
                                    el('u', null, 'Place ID')
                                ),
                                el(
                                    'small',
                                    {style: {fontSize: '13px', color: '#000'}},
                                    'If you can\'t find your place on this map, please read ',
                                    el('a', {
                                        href: GRW_VARS.supportUrl + '&grw_tab=fig#place_id',
                                        target: '_blank'
                                    }, 'this manual how to find any Google Place ID'),
                                    '.'
                                ),
                                el(
                                    'p',
                                    null,
                                    el('span', null, '2'),
                                    ' Paste copied Place ID in this field and select language if needed ',
                                    el(wp.components.TextControl, {
                                        type: 'text',
                                        className: 'grw-connect-id',
                                        placeholder: 'Place ID'
                                    }),
                                    LangControl('Choose language if needed')
                                ),
                                el(
                                    'p',
                                    null,
                                    el('span', null, '3'),
                                    ' Click CONNECT GOOGLE buttons'
                                ),
                                el('button', {className: 'grw-connect-btn', onClick: connectGoogle}, 'Connect Google'),
                                el('small', {className: 'grw-connect-error'})
                            )
                        );

                        //End of control panel
                    },
                    save: props => {
                        return [props.pagination,props.attributes.text_size];
                    },


                })
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