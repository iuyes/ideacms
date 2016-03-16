/*
* @Author: frank
* @Date:   2016-03-15 10:14:43
* @Last Modified by:   frank
* @Last Modified time: 2016-03-15 10:23:25
*/

var secs = 60;
document.agree.agreeb.disabled=true;
for (var i = 1; i <= secs; i++) {
  window.setTimeout("update("+ i +")", i*1000);
}

function update(num){
  if(num == secs){
    document.agree.agreeb.disabled=false;
  }else{
    var printnr = secs-num;
    document.agree.agreeb.value="请务必认真查看<安装协议>("+ printnr +")";
  }
}
