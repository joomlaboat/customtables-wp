(function( $ ) {
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


	$( document ).ready(function() {
		$('[data-toggle="tab"]').click(function () {
			let tabs = jQuery(this).attr('data-tabs');
			let tab = jQuery(this).attr("data-tab");
			$(tabs).find(".gtab").removeClass("active");
			$('[data-toggle="tab"]').removeClass("nav-tab-active");
			$(tabs).find(tab).addClass("active");
			jQuery(this).addClass("nav-tab-active");
		});
	});

})( jQuery );


function readmoreOpenClose(itemid)
{
	var obj=document.getElementById(itemid);
	var c=obj.className;
	if(c.indexOf("ct_readmoreOpen")!=-1)
		c=c.replace("ct_readmoreOpen","ct_readmoreClose");
	else if(c.indexOf("ct_readmoreClosed")!=-1)
		c=c.replace("ct_readmoreClosed","ct_readmoreOpen");
	else if(c.indexOf("ct_readmoreClose")!=-1)
		c=c.replace("ct_readmoreClose","ct_readmoreOpen");

	obj.className=c;
}