//
// Scroll to (anchor links)
//
"use strict";var ScrollTo=function(){var t=$(".scroll-me, [data-scroll-to], .toc-entry a");function o(t){var o=t.attr("href"),l=t.data("scroll-to-offset")?t.data("scroll-to-offset"):0,a={scrollTop:$(o).offset().top-l};$("html, body").stop(!0,!0).animate(a,600),event.preventDefault()}t.length&&t.on("click",function(t){o($(this))})}();