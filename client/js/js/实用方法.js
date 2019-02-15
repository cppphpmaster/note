/**
 * 倒计时
 */
var i = 5;
var intervalid;
intervalid = setInterval("fun()", 1000);
function fun() {
    if (i == 0) {
        window.location.href = "../index.html";
        clearInterval(intervalid);
    }
    document.getElementById("mes").innerHTML = i;
    i--;
}


/**
 * 获取get请求参数
 * @param variable
 * @returns {*}
 */
function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return pair[1];
        }
    }
    return (false);
}
// 使用: var amount = getQueryVariable('amount');