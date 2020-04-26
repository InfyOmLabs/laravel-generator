//
// Dropzone
//
"use strict";var Dropzones=function(){var e=$('[data-toggle="dropzone"]'),i=$(".dz-preview");e.length&&(Dropzone.autoDiscover=!1,e.each(function(){var e,t,n,o,l;e=$(this),t=void 0!==e.data("dropzone-multiple"),n=e.find(i),o=void 0,l={url:e.data("dropzone-url"),thumbnailWidth:null,thumbnailHeight:null,previewsContainer:n.get(0),previewTemplate:n.html(),maxFiles:t?null:1,acceptedFiles:t?null:"image/*",init:function(){this.on("addedfile",function(e){!t&&o&&this.removeFile(o),o=e})}},n.html(""),e.dropzone(l)}))}();