//
// Icon code copy/paste
//
"use strict";var CopyIcon=function(){var t,o=".btn-icon-clipboard",i=$(o);i.length&&((t=i).tooltip().on("mouseleave",function(){t.tooltip("hide")}),new ClipboardJS(o).on("success",function(t){$(t.trigger).attr("title","Copied!").tooltip("_fixTitle").tooltip("show").attr("title","Copy to clipboard").tooltip("_fixTitle"),t.clearSelection()}))}();