$(".switchs").each(function(i){
    			var ul = $(this).parent().next();
    			$(this).click(
    			function(){
    				if(ul.is(':visible')){
    					ul.hide();
    					$(this).removeClass('on');
    						}else{
    					ul.show();
    					$(this).addClass('on');
    				}
    			})
    		});