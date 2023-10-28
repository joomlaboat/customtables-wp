const {registerBlockType} = wp.blocks;
const {SelectControl} = wp.components;

// Get helper functions from global scope
//const { registerBlockType } = window.wp.blocks;
const { __ } = window.wp.i18n;


/*



(function(blocks, editor, element, components, api) {


    var el = element.createElement,
        InspectorControls = editor.InspectorControls,
        PanelBody = components.PanelBody,
        SelectControl = components.SelectControl,
        TextControl = components.TextControl;

    alert("ssssssssss");
*/
//blocks.registerBlockType('customtables/customtables-block', {
registerBlockType('customtables/dynamic-block', {
    title: 'CustomTables Block',
    icon: 'smiley',
    category: 'common',
    attributes: {
        connections: {
            type: 'array',
            default: [''],
            query: {
                id: {type: 'string',},
                name: {type: 'string',},
                photo: {type: 'string',},
                refresh: {type: 'boolean',},
                local_img: {type: 'boolean',},
                platform: {type: 'string',},
            }
        },
        view_mode: {
            type: 'string'
        },
        pagination: {
            type: 'string'
        },
        text_size: {
            type: 'string'
        },
    },

    edit: function (props) {

        //var el                = element.createElement;

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
    },

    save: props => {
        return [props.pagination,props.attributes.text_size];
/*
        return wp.element.createElement(
            'div',
            null,
            'Hello World'
        );
        let el = wp.element.createElement;
        */
        return wp.element.createElement(
            'div',
            null,
            'Hello World'
        );
    },
});
