
// JavaScript Document
(function() {
	//tinymce.PluginManager.requireLangPack('easymail');


    // Creates a new plugin class and a custom listbox
    tinymce.create('tinymce.plugins.easymail', {
		init : function(ed, url) {
			the_editor = ed;
		},

        createControl: function(n, cm) {
            switch (n) {
                case 'easymail':
                    var mlb = cm.createListBox('easymail', {
                        title : 'EasyMail',
                        onselect : function(v) {
							if ( v != '' ) {
								//sel_content = tinyMCE.activeEditor.selection.getContent();
								//tinyMCE.activeEditor.selection.setContent("[tag-1]" + sel_content + "[/tag-1]");
								//tinyMCE.activeEditor.windowManager.alert('Value selected:' + v);
								tinyMCE.activeEditor.selection.setContent(v);
							}
                        }
                    });

                    if ( alo_em_tinymce_labels ) {
						for (var l in alo_em_tinymce_labels)
						{
							mlb.add(alo_em_tinymce_labels[l], '');
							if ( alo_em_tinymce_tags[l] ) {
								for (var t in alo_em_tinymce_tags[l])
								{
									mlb.add(alo_em_tinymce_tags[l][t], alo_em_tinymce_tags[l][t]);
								}
							}
						}
					}

                // Return the new listbox instance
                return mlb;
            }
            return null;
        }
    });
    tinymce.PluginManager.add('easymail', tinymce.plugins.easymail);
})();


/*
 // TODO tinymce 4
 (function() {
 tinymce.PluginManager.add('easymail', function( editor, url ) {
     editor.addButton( 'easymail', {
         title: 'shortcodes',
         text: '[shortcodes]',
         type: 'menubutton',
         icon: 'icon easymail-tinymce-icon',
             menu: [
             {
                 text: 'Menu item I',
                 value: 'Text from menu item I',
                 onclick: function() {
                    editor.insertContent(this.value());
                 }
             }
         ]
         });
     });
 })();
 */