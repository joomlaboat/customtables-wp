<?php

/**
 * The admin area of the plugin to load the User List Table
 */
?>

<div class="wrap ct_doc">
    <h2><?php _e('Custom Tables - Database Schema', $this->plugin_text_domain); ?></h2>

    <h2 class="nav-tab-wrapper wp-clearfix">
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-1" class="nav-tab nav-tab-active">Tab 1
        </button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="nav-tab">Tab 2a</button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-3" class="nav-tab">Tab 3</button>
    </h2>

    <div class="gtabs demo">
        <div class="gtab active tab-1">
            <h1>Gtab 1</h1>
            <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="ui button">Tab 2</button>
        </div>

        <div class="gtab tab-2">
            <h1>Gtab 2</h1>
            <p> 1Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi minima fugit, est facere molestiae quod
                pariatur. Consectetur natus, blanditiis laborum possimus doloremque harum adipiscelit. Nisi minima
                fugit, est facere molestiae quod pariatur. Consectetur natus, blanditiis laborum possimus doloremque
                harum adipisci debitis similique, nostrum provident ut dolelit. Nisi minima fugit, est facere molestiae
                quod pariatur. Consectetur natus, blanditiis laborum possimus doloremque harum adipisci debitis
                similique, nostrum provident ut dolelit. Nisi minima fugit, est facere molestiae quod pariatur.
                Consectetur natus, blanditiis laborum possimus doloremque harum adipisci debitis similique, nostrum
                provident ut doli debitis similique, nostrum provident ut dolore. </p>
        </div>

        <div class="gtab tab-3"><h1>3Gtab 3</h1></div>
    </div>

</div>

<hr/>
<p>
    2 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi consequatur qui nostrum deleniti, quaerat.
    Voluptate quisquam nulla, sit error, quas mollitia sint veniam at rem corporis dolore, eaque sapiente qui.
</p>
