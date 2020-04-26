//
// Checklist
//
"use strict";var Checklist=function(){var c=$('[data-toggle="checklist"]');function e(c){c.is(":checked")?c.closest(".checklist-item").addClass("checklist-item-checked"):c.closest(".checklist-item").removeClass("checklist-item-checked")}c.length&&(c.each(function(){$(this).find('.checklist-entry input[type="checkbox"]').each(function(){e($(this))})}),c.find('input[type="checkbox"]').on("change",function(){e($(this))}))}();