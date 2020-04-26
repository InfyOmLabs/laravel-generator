//
// Bars chart
//
var BarsChart=function(){var a=$("#chart-bars");a.length&&function(a){var t=new Chart(a,{type:"bar",data:{labels:["Jul","Aug","Sep","Oct","Nov","Dec"],datasets:[{label:"Sales",data:[25,20,30,22,17,29]}]}});a.data("chart",t)}(a)}();