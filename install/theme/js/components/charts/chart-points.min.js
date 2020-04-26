//
// Charts
//
"use strict";var PointsChart=function(){var a,t,o=$("#chart-points");o.length&&(a=o,t=new Chart(a,{type:"line",options:{scales:{yAxes:[{gridLines:{color:Charts.colors.gray[200],zeroLineColor:Charts.colors.gray[200]},ticks:{}}]}},data:{labels:["May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],datasets:[{label:"Performance",data:[10,18,28,23,28,40,36,46,52],pointRadius:10,pointHoverRadius:15,showLine:!1}]}}),a.data("chart",t))}();