//
// List.js
//
"use strict";var SortList=function(){var t=$('[data-toggle="list"]'),a=$("[data-sort]");t.length&&t.each(function(){var t;t=$(this),new List(t.get(0),function(t){return{valueNames:t.data("list-values"),listClass:t.data("list-class")?t.data("list-class"):"list"}}(t))}),a.on("click",function(){return!1})}();