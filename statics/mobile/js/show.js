$(document).ready(function(){
	$("#content img").load(function(){
		var width = this.width;
		if (width > $(window).width()) {
			width = $(window).width() - 20;
		}
		$(this).attr("width", width);
	});
	$("#banner_close").click(function(){
		$("#common-banner").hide();
	});
	$("#nav-more").click(function(){
		history.back();
	});
	$("#deal .deal-box h1").click(function(){
		$(this).toggleClass("current");
		var str = $(this).attr("class");
		if (str == "tag") {
			$(this).next().hide();
		} else {
			$(this).next().show();
		}
	});
});