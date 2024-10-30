(function(){
  tinymce.create('tinymce.plugins.lukas_tripster', {
    init: function(ed, url){
      ed.addButton('lukas_tripster', {
        title: 'Tripster',
        cmd: 'lukas_tripsterCmd',
        image: url + '/images/tripster-logo.png'
      });
      ed.addCommand('lukas_tripsterCmd', function(){
        var selectedText = ed.selection.getContent();
        var win = ed.windowManager.open({
          title: ed.getLang('lukas_tripster_tinymce_plugin.Settings Title'),
          body: [
            {
              type: 'textbox',
              name: 'city',
              label: ed.getLang('lukas_tripster_tinymce_plugin.City'),
              minWidth: 250,
              value: ''
            },
            {
              type: 'textbox',
              name: 'number',
              label: ed.getLang('lukas_tripster_tinymce_plugin.Number of block'),
              minWidth: 50,
              value : ''
            },
          ],
          buttons: [
            {
              text: "Ok",
              subtype: "primary",
              onclick: function() {
                win.submit();
              }
            },
            {
              text: ed.getLang('lukas_tripster_tinymce_plugin.Skip'),
              onclick: function() {
                win.close();
                var returnText = '' + selectedText + '';
                ed.execCommand('mceInsertContent', 0, returnText);
              }
            },
            {
              text: "Cancel",
              onclick: function() {
                win.close();
              }
            }
          ],
          onsubmit: function(e){
            var params = [];
            if( e.data.city.length > 0 ) {
              params.push('city="' + e.data.city + '"');
            }
            if( e.data.number.length > 0 ) {
              params.push('number="' + e.data.number + '"');
            }
            if( params.length > 0 ) {
              paramsString = ' ' + params.join(' ');
            }
            var returnText = '[tripster ' + paramsString + ']';
            ed.execCommand('mceInsertContent', 0, returnText);
          }
        });
      });
    },
    getInfo: function() {
      return {
        longname : 'Tripster Button',
        author : 'Konstantin Lukas',
        authorurl : 'https://github.com/servekon',
        version : "0.1"
      };
    }
  });
  tinymce.PluginManager.add( 'lukasTripster', tinymce.plugins.lukas_tripster );
})();
