//
// Onscreen - viewport checker
//
"use strict";var OnScreen=function(){var n,e=$('[data-toggle="on-screen"]');e.length&&(n={container:window,direction:"vertical",doIn:function(){},doOut:function(){},tolerance:200,throttle:50,toggleClass:"on-screen",debug:!1},e.onScreen(n))}();