//
// Charts
//
"use strict";var SalesChart=function(){var a,r,e=$("#chart-sales-dark");e.length&&(a=e,r=new Chart(a,{type:"line",options:{scales:{yAxes:[{gridLines:{color:Charts.colors.gray[700],zeroLineColor:Charts.colors.gray[700]},ticks:{}}]}},data:{labels:["May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],datasets:[{label:"Performance",data:[0,20,10,30,15,40,20,60,60]}]}}),a.data("chart",r))}();