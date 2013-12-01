
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

                /*
                case 'easymail':
                var c = cm.createSplitButton('easymail', {
                    title : 'My split button',
                    image : 'img/example.gif',
                    onclick : function() {
                        tinyMCE.activeEditor.windowManager.alert('Button was clicked.');
                    }
                });

                c.onRenderMenu.add(function(c, m) {
                    m.add({title : 'Some title', 'class' : 'mceMenuItemTitle'}).setDisabled(1);

                    m.add({title : 'Some item 1', onclick : function() {
                        tinyMCE.activeEditor.windowManager.alert('Some  item 1 was clicked.');
                    }});

                    m.add({title : 'Some item 2', onclick : function() {
                        tinyMCE.activeEditor.windowManager.alert('Some  item 2 was clicked.');
                    }});
                });

                // Return the new splitbutton instance
                return c;
                */
            }
            return null;
        }
    });
    tinymce.PluginManager.add('easymail', tinymce.plugins.easymail);
})();




/*
function tiny_plugin() {
    return "[tiny-plugin]";
}
 
(function() {
    tinymce.create('tinymce.plugins.tinyplugin', {
 
        init : function(ed, url){
            ed.addButton('tinyplugin', {
            title : 'Insert TinyPlugin',
                onclick : function() {
                    ed.execCommand(
                    'mceInsertContent',
                    false,
                    tiny_plugin()
                    );
                },
                image: url + "/wand.png"
            });
        }
    });
 
    tinymce.PluginManager.add('tinyplugin', tinymce.plugins.tinyplugin);
 
})();
*/

/*
(function() {
tinymce.create('tinymce.plugins.tinyplugin', {
    createControl: function(n, cm) {
        switch (n) {
            case 'mylistbox':
                var mlb = cm.createListBox('mylistbox', {
                     title : 'My list box',
                     onselect : function(v) {
                         tinyMCE.activeEditor.windowManager.alert('Value selected:' + v);
                     }
                });

                // Add some values to the list box
                mlb.add('Some item 1', 'val1');
                mlb.add('some item 2', 'val2');
                mlb.add('some item 3', 'val3');

                // Return the new listbox instance
                return mlb;

            case 'mysplitbutton':
                var c = cm.createSplitButton('mysplitbutton', {
                    title : 'My split button',
                    //image : 'img/example.gif',
                    onclick : function() {
                        tinyMCE.activeEditor.windowManager.alert('Button was clicked.');
                    }
                });

                c.onRenderMenu.add(function(c, m) {
                    m.add({title : 'Some title', 'class' : 'mceMenuItemTitle'}).setDisabled(1);

                    m.add({title : 'Some item 1', onclick : function() {
                        tinyMCE.activeEditor.windowManager.alert('Some  item 1 was clicked.');
                    }});

                    m.add({title : 'Some item 2', onclick : function() {
                        tinyMCE.activeEditor.windowManager.alert('Some  item 2 was clicked.');
                    }});
                });

                // Return the new splitbutton instance
                return c;
        }

        return null;
    }
});

})();
*/
