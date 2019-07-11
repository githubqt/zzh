//------------------格式化时间为 yyyy-MM-dd ---------------------------------------  
$.fn.datebox.defaults.formatter = function(date) {  
var y = date.getFullYear();  
var m = date.getMonth() + 1;  
var d = date.getDate();  
return y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);  
};  
//  
$.fn.datebox.defaults.parser = function(s) {  
if (s) {  
var a = s.split('-');  
var d = new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]));  
return d;  
} else {  
return new Date();  
}  
  
}; 
