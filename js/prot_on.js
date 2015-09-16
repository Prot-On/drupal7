function protOnLogin(node){
	var basepath = window.location.pathname;
	var url = basepath + 'prot_on/oauth';
	if(node){
		url += '/' + node;
	}
	window.location.href = url;
}


jQuery(document).ready(function(){
	
	var dndViewer = '<div id="DndViewer"><iframe id="iDndViewer" scrolling="no" src="" class="iDndViewer"></iframe></div>';
	jQuery('body').append(dndViewer);
	
	jQuery('a').each(function(){
		var link = jQuery(this);
		var href = link.attr('href');
		var pattern = /(.+).proton.(.+)/;
		if(pattern.test(href)) {
			if(!link.attr('data-link')){
				link.attr('data-link', 'dnd');
			}
		}
	});
	
	jQuery('[data-link="dnd"]').click(function(event){
		event.preventDefault();
		jQuery('#DndViewer').dialog({
			title: "Prot-On Drag & Drop",
			width: "90%",
			height: 500,
			dialogClass: 'fixed-dialog'
		});
		jQuery('#iDndViewer').attr('src', jQuery(this).attr('href'));
	});
	
	// Hack to convert oAuth submit in a button
	if(document.getElementById('oAuthButton')){
		document.getElementById('oAuthButton').type='button';
	}
	
});