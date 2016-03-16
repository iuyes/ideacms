/*
 * IdeaCMS
 */
if (undefined == ideacms_admin_document) var ideacms_admin_document="";
function preview(obj) {
	var filepath = $('#'+obj).val();
	if (filepath) {
		var content = '<img src="'+filepath+'?'+Math.random()+'" onload="if(this.width>$(window).width()/2)this.width=$(window).width()/2;" />';
	} else {
		var content = idec_lang[0];
	}
	window.top.art.dialog({title:idec_lang[1],fixed:true, content: content});
}

function file_info(obj) {
	var filepath = $('#'+obj).val();
	var content = idec_lang[26];
	if (filepath) {
		$.post(sitepath+'?c=api&a=fileinfo&id='+Math.random(), { file:filepath }, function(data){ 
			window.top.art.dialog({title:idec_lang[25],fixed:true, content: data});
		});
	} else {
		window.top.art.dialog({title:idec_lang[25],fixed:true, content: content});
	}
}

function uploadImage(obj, w, h, size) {
	var url = sitepath+'?c=attachment&a=image&w='+w+'&h='+h+'&size='+size+'&file='+$("#"+obj).val()+'&document='+ideacms_admin_document;
	var winid = 'win_'+obj;
	window.top.art.dialog(
	    {id:winid, okVal:idec_lang[6], cancelVal:idec_lang[7], iframe:url, title:idec_lang[3], width:'470', height:'150', lock:true}, 
		function(){
		    var d = window.top.art.dialog({id:winid}).data.iframe;
			var filename = d.document.getElementById('filename').value;
			if (filename) {
				$("#"+obj).val(filename);
			} else {
			    alert(idec_lang[24]);
				return false;
			}
		},
		function(){
			window.top.art.dialog({id:winid}).close();
	    }
	);
	void(0);
}

function uploadFile(obj, type, size) {
	var url = sitepath+'?c=attachment&a=file&type='+type+'&size='+size+'&file='+$("#"+obj).val()+'&document='+ideacms_admin_document;
	var winid = 'win_'+obj;
	window.top.art.dialog(
	    {id:winid, okVal:idec_lang[6], cancelVal:idec_lang[7], iframe:url, title:idec_lang[3], width:'470', height:'150', lock:true}, 
		function(){
		    var d = window.top.art.dialog({id:winid}).data.iframe;
			var filename = d.document.getElementById('filename').value;
			if (filename) {
				$("#"+obj).val(filename);
			} else {
			    alert(idec_lang[24]);
				return false;
			}
		},
		function(){
			window.top.art.dialog({id:winid}).close();
	    }
	);
	void(0);
}

function uploadFiles(obj, setting) {
	var url = sitepath+'?c=attachment&a=files&setting='+setting+'&document='+ideacms_admin_document;
	var winid = 'win_'+obj;
	window.top.art.dialog(
	    {id:winid, okVal:idec_lang[6], cancelVal:idec_lang[7], iframe:url, title:idec_lang[3], width:'500', height:'420', lock:true}, 
		function(){
		    var d = window.top.art.dialog({id:winid}).data.iframe;
			var files = d.document.getElementById('att-status').innerHTML;
			var names = d.document.getElementById('att-name').innerHTML;
			var file = files.split('|');
			var name = names.split('|');
			for (var id in file) {
				var filepath = file[id];
				var filename = name[id];
	            if (filepath) {
					var c = '<li id="files_'+id+'">';
					c += '<input type="text" class="input-text" style="width:310px;" value="'+filepath+'" name="data['+obj+'][file][]">';
					c +='<input type="text" class="input-text" style="width:160px;" value="'+filename+'" name="data['+obj+'][alt][]">';
					c += '<a href="javascript:removediv(\''+id+'\');">'+idec_lang[2]+'</a> <a href="javascript:;" style="cursor:move;">'+idec_lang[4]+'</a></li>';
					$('#'+obj+'-sort-items').append(c);
				}
			}
			
		},
		function(){
			window.top.art.dialog({id:winid}).close();
	    }
	);
	void(0);
}

function get_kw() {
	$.post(sitepath+'?c=api&a=ajaxkw&id='+Math.random(), { data:$('#title').val() }, function(data){ 
        if(data && $('#keywords').val()=='') $('#keywords').val(data); 
	});
}

function removediv(fileid) {
	$('#files_'+fileid).remove();
}

function add_null_file(obj) {
    var id= parseInt(Math.random()*1000);
    var c = '<li id="files_'+id+'">';
	c += '<input type="text" class="input-text" style="width:310px;" value="" name="data['+obj+'][file][]">';
	c +='<input type="text" class="input-text" style="width:160px;" value="" name="data['+obj+'][alt][]">';
	c += '<a href="javascript:removediv(\''+id+'\');">'+idec_lang[2]+'</a> <a href="javascript:;" style="cursor:move;" title="'+idec_lang[5]+'">'+idec_lang[4]+'</a></li>';
	$('#'+obj+'-sort-items').append(c);
}