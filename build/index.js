let customtables_tables = [];
let customtables_layouts = [];
let customtables_prerenderedContent = [];

const definesUtilityFunction = function () {

    // Retrieve the 'blocks' object from the 'wp' namespace in the window
    var blocks = window.wp.blocks;

    // Define metadata for a custom block in WordPress
    var blockMetadata = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"customtables/dynamic-block","version":"0.1.0","title":"Example: Dynamic Block (ESNext)","category":"text","icon":"universal-access-alt",' +
        '"attributes":{"message":{}},"example":{"attributes":{"message":"CustomTables Block"}},"supports":{"html":false},"textdomain":"dynamic-block","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css"}');

    // Load list of tables, layouts and render the block and the side panel
    CustomTablesLoadTables();
    CustomTablesLoadLayouts();

    // Destructure 'name' property from 'blockMetadata'
    const {name: i} = blockMetadata;

    // Render the custom block
    CustomTablesRenderBlock(blocks, i);
};

definesUtilityFunction();

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

function CustomTablesLoadPreview(newAttributes, props) {
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

                let blockId = cyrb53(JSON.stringify(newAttributes));
                console.log("blockId:" + blockId);
                customtables_prerenderedContent[blockId] = text;
                console.log("updated");
                console.log(typeof props);

                setTimeout(function () {
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
            let blockProps = wp.blockEditor.useBlockProps();

            props.attributes.loading = 0;
            let blockId = cyrb53(JSON.stringify(props.attributes));

            let el = wp.element.createElement;
            let InspectorControls = wp.editor.InspectorControls;
            let PanelBody = wp.components.PanelBody;
            let SelectControl = wp.components.SelectControl;
            let TextControl = wp.components.TextControl;
            let __ = wp.i18n.__;
            let generatedPreview;

            if (customtables_prerenderedContent[blockId] === undefined) {
                CustomTablesLoadPreview(props.attributes, props);
            } else {
                generatedPreview = (customtables_prerenderedContent[blockId] !== '' ? customtables_prerenderedContent[blockId] : '<p>CustomTables Block:<br/>Please select a Table and Layout.</p>')
            }

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
                            TextControl,
                            {
                                id: 'customtables_block_loading',
                                name: 'customtables_block_loading',
                                value: 0,
                                /*options: [{label: 'state 0',value: 0},{label: 'state 1',value: 1}],*/
                                onChange: function (newValue) {
                                    console.log("on change called");
                                    props.setAttributes({loading: 0});
                                },
                                type: 'hidden'
                            }
                        ), el(
                            PanelBody,
                            {
                                title: __('Table'),
                                initialOpen: true,
                                className: '2grw-toggle grw-builder-connect 2grw-connect-business'
                            },

                            el(
                                SelectControl,
                                {
                                    id: 'customtables_block_table',
                                    name: 'customtables_block_table',
                                    value: props.attributes.table,
                                    options: customtables_tables,
                                    onChange: function (newValue) {
                                        props.setAttributes({table: newValue});
                                        props.setAttributes({loading: 0});

                                        let newAttributes = props.attributes;
                                        newAttributes.table = newValue;

                                        CustomTablesLoadPreview(newAttributes, props);
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
                                    id: 'customtables_block_layout',
                                    name: 'customtables_block_layout',
                                    value: props.attributes.layout,
                                    options: customtables_layouts,
                                    onChange: function (newValue) {
                                        props.setAttributes({layout: newValue});
                                        props.setAttributes({loading: 0});

                                        let newAttributes = props.attributes;
                                        newAttributes.layout = newValue;

                                        CustomTablesLoadPreview(newAttributes, props);
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

                                        CustomTablesLoadPreview(newAttributes, props);
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

                                        CustomTablesLoadPreview(newAttributes, props);
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

                                        CustomTablesLoadPreview(newAttributes, props);
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
            props.attributes.loading = 1;
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
            return newAttributes;
        },
    })
}

const cyrb53 = (str, seed = 0) => {
    let h1 = 0xdeadbeef ^ seed, h2 = 0x41c6ce57 ^ seed;
    for (let i = 0, ch; i < str.length; i++) {
        ch = str.charCodeAt(i);
        h1 = Math.imul(h1 ^ ch, 2654435761);
        h2 = Math.imul(h2 ^ ch, 1597334677);
    }
    h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507);
    h1 ^= Math.imul(h2 ^ (h2 >>> 13), 3266489909);
    h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507);
    h2 ^= Math.imul(h1 ^ (h1 >>> 13), 3266489909);

    return 4294967296 * (2097151 & h2) + (h1 >>> 0);
};

