//
// Popover
//
"use strict";var Popover=function(){var o=$('[data-toggle="popover"]'),r="";o.length&&o.each(function(){!function(o){o.data("color")&&(r="popover-"+o.data("color"));var a={trigger:"focus",template:'<div class="popover '+r+'" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'};o.popover(a)}($(this))})}();