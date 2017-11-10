/**
 * Handle the TXT button from the content toolbar
 */
function fnPluginHTMLTXT() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - TXT');
	}
	/*<!-- endbuild -->*/

	// When the user has clicked on a note from the treeview, the jstree_init()
	// function (from jstree.js) has initialized the marknotes.note.url variable
	// to 'objNode.data.url' i.e. the data-url info of that node and that info
	// is set by the PHP listFiles task to the relative filename of the note
	// with the .html extension (so, f.i. folder/documentation/marknotes.html)
	// (and not the full URL like http://localhost/docs/folder/documentation/marknotes.html)
	if (marknotes.note.url == '') {
		// The user click on the Reveal button but should first select
		// a note in the treeview
		Noty({
			message: $.i18n('error_select_first'),
			type: 'error'
		});
	} else {
		var article = $.trim($('article').text()); // use .html() if tags are needed
		var blob = new Blob([article], {
			type: "text/plain; charset=utf-8"
		});
		saveAs(blob, marknotes.note.basename + ".txt");
	}
	return true;
}
