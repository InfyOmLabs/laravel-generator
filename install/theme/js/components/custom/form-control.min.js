//
// Form control
//
"use strict";var FormControl=function(){var o=$(".form-control");o.length&&o.on("focus blur",function(o){$(this).parents(".form-group").toggleClass("focused","focus"===o.type)}).trigger("blur")}();