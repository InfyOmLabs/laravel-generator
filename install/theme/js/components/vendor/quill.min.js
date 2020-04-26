//
// Quill.js
//
"use strict";var QuillEditor=function(){var l=$('[data-toggle="quill"]');l.length&&l.each(function(){var l,e;l=$(this),e=l.data("quill-placeholder"),new Quill(l.get(0),{modules:{toolbar:[["bold","italic"],["link","blockquote","code","image"],[{list:"ordered"},{list:"bullet"}]]},placeholder:e,theme:"snow"})})}();