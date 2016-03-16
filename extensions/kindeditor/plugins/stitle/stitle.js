KindEditor.plugin('stitle', function(K) {
    var editor = this, name = 'stitle';
	editor.clickToolbar(name, function() {
		var lang = editor.lang(name + '.'),
			html = ['<div style="padding:10px 20px;">',
				'<div class="ke-dialog-row">',
				'</div>',
				editor.lang('stitle2')+'<input class="ke-input-text" type="text" value="" style="width:320px;" /> ',
				'<div class="ke-dialog-row" style="padding-top:5px;">',
				editor.lang('stitle1'),
				'</div>',
				'</div>'].join(''),
			dialog = editor.createDialog({
				name : name,
				width : 500,
				title : editor.lang(name),
				body : html,
				yesBtn : {
					name : editor.lang('yes'),
					click : function(e) {
						var type = K('.ke-code-type', dialog.div).val(),
							code = input.val(),
							html = '[stitle]' + K.escape(code) + '[/stitle]';
						if (code !='') {
							editor.insertHtml(html).hideDialog().focus();
						} else {
							alert(editor.lang('stitle3'));
						}
					}
				}
			}),
			input = K('input', dialog.div);
		input[0].focus();
	});
});

