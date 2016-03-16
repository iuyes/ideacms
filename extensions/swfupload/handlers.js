function att_show(serverData,file)
{
	var serverData = serverData.replace(/<div.*?<\/div>/g,'');
	var data = serverData.split(',');
	var id = data[0];
	var src = data[1];
	var ext = data[2];
	var filename = data[3];
	if(id == 0) {
		alert(src)
		return false;
	}
	if(ext == 1) {
		var img = '<a href="javascript:;" onclick="javascript:att_cancel(this,'+id+',\'upload\')" class="on"><div class="icon"></div><img src="'+src+'" width="80" imgid="'+id+'" path="'+src+'" title="'+filename+'"/></a>';
	} else {
		var img = '<a href="javascript:;" onclick="javascript:att_cancel(this,'+id+',\'upload\')" class="on"><div class="icon"></div><img src="'+viewpath+'ext/'+ext+'.gif" width="80" imgid="'+id+'" path="'+src+'" title="'+filename+'"/></a>';
	}
	
	$('#fsUploadProgress').append('<li><div id="attachment_'+id+'" class="img-wrap">'+img+'</div></li>');
	$('#att-status').append('|'+src);
	$('#att-name').append('|'+filename);
}
function att_insert(obj,id)
{
	var uploadfile = $("#attachment_"+id+"> img").attr('path');
	$('#att-status').append('|'+uploadfile);
}

function att_cancel(obj,id,source){
	var src = $(obj).children("img").attr("path");
	var filename = $(obj).children("img").attr("title");
	if($(obj).hasClass('on')){
		$(obj).removeClass("on");
		var imgstr = $("#att-status").html();
		var length = $("a[class='on']").children("img").length;
		var strs = filenames = '';
		for(var i=0;i<length;i++){
			strs += '|'+$("a[class='on']").children("img").eq(i).attr('path');
			filenames += '|'+$("a[class='on']").children("img").eq(i).attr('title');
		}
		$('#att-status').html(strs);
		$('#att-name').html(filenames);
		if(source=='upload') $('#att-status-del').append('|'+id);
	} else {
		$(obj).addClass("on");
		$('#att-status').append('|'+src);
		$('#att-name').append('|'+filename);
		var imgstr_del = $("#att-status-del").html();
		var imgstr_del_obj = $("a[class!='on']").children("img")
		var length_del = imgstr_del_obj.length;
		var strs_del='';
		for(var i=0;i<length_del;i++){strs_del += '|'+imgstr_del_obj.eq(i).attr('imgid');}
		if(source=='upload') $('#att-status-del').html(strs_del);
	}
}
//swfupload functions
function fileDialogStart() {
	/* I don't need to do anything here */
}
function fileQueued(file) {
	if(file!= null){
		try {
			var progress = new FileProgress(file, this.customSettings.progressTarget);
			progress.toggleCancel(true, this);
		} catch (ex) {
			this.debug(ex);
		}
	}
}

function fileDialogComplete(numFilesSelected, numFilesQueued)
{
	try {
		if (this.getStats().files_queued > 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}
		
		/* I want auto start and I can do that here */
		//this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}
function uploadStart(file)
{
	var progress = new FileProgress(file, this.customSettings.progressTarget);
	progress.setStatus(idec_lang[8]);
	return true;
}
function uploadProgress(file, bytesLoaded, bytesTotal)
{
	var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
	var progress = new FileProgress(file, this.customSettings.progressTarget);
	progress.setProgress(percent);
	progress.setStatus(idec_lang[9]+"("+percent+" %)"+idec_lang[10]);
}
function uploadSuccess(file, serverData)
{
	att_show(serverData,file);
	var progress = new FileProgress(file, this.customSettings.progressTarget);
	progress.setComplete();
	progress.setStatus(idec_lang[11]);
}
function uploadComplete(file)
{
	if (this.getStats().files_queued > 0)
	{
		 this.startUpload();
	}
}
function uploadError(file, errorCode, message) {
	var msg;
	switch (errorCode)
	{
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			msg = idec_lang[12] + "：" + message;
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			msg = idec_lang[12];
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			msg = idec_lang[13];
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			msg = idec_lang[14];
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			msg = idec_lang[15];
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			msg = idec_lang[16];
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			msg = idec_lang[17];
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			msg = idec_lang[18]+swfu.settings.file_upload_limit;
			break;
		default:
			msg = message;
			break;
		}
	var progress = new FileProgress(file,this.customSettings.progressTarget);
	progress.setError();
	progress.setStatus(msg);
}

function fileQueueError(file, errorCode, message)
{
	var errormsg;
	switch (errorCode) {
	case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
		errormsg = idec_lang[19];
		break;
	case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
		errormsg = idec_lang[20];
		break;
	case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
		errormsg = idec_lang[21];
		break;
	case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
		errormsg = idec_lang[22];
	default:
		errormsg = idec_lang[23];
		break;
	}

	var progress = new FileProgress('file',this.customSettings.progressTarget);
	progress.setError();
	progress.setStatus(errormsg);

}

