//
// Charts
//
"use strict";var DoughnutChart=function(){var a,t,r,e=$("#chart-doughnut");e.length&&(a=e,t=function(){return Math.round(100*Math.random())},r=new Chart(a,{type:"doughnut",data:{labels:["Danger","Warning","Success","Primary","Info"],datasets:[{data:[t(),t(),t(),t(),t()],backgroundColor:[Charts.colors.theme.danger,Charts.colors.theme.warning,Charts.colors.theme.success,Charts.colors.theme.primary,Charts.colors.theme.info],label:"Dataset 1"}]},options:{responsive:!0,legend:{position:"top"},animation:{animateScale:!0,animateRotate:!0}}}),a.data("chart",r))}();