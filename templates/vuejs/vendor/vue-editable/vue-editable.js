(function() {
	var VueEditable = {
		install: function(Vue) {
			Vue.prototype.$editable = function(e,callback) {
				var target = e.target, value = target.innerText;
				target.innerHTML = "<input type='text' value='" + value + "' id='_editable' style='width:100%;box-sizing:border-box;background:transparent;font-size:13px;color:red;text-align:center'>";
				var input = document.getElementById('_editable');
				input.focus();
				var len = input.value.length;
				if (document.selection) {
					var sel = input.createTextRange();
					sel.moveStart('character', len);
					sel.collapse();
					sel.select();
				} else if (typeof input.selectionStart == 'number' && typeof input.selectionEnd == 'number') {
					input.selectionStart = input.selectionEnd = len;
				}

				var update = function() {
					var newValue = this.value;
					if (value != newValue && newValue != '') {
						target.innerHTML = newValue;
						callback(newValue)
					} else {
						target.innerHTML = value;
					}
					//input.removeEventListener("blur", null, false);
					input.removeEventListener("keypress", update, false);					
				};

				var reinit = function() {
					if (value != this.value && this.value != '') 
						target.innerHTML = value;
					e.stopImmediatePropagation();
					e.preventDefault();
					input.removeEventListener("blur", reinit, false);
				}

				input.addEventListener("blur", update, false); 
				input.addEventListener('keypress', function (e) {
				    var key = e.which || e.keyCode;
				    if (key === 13) {
						update();
			    	 	input.removeEventListener("blur", update, false);
				    }
				});					
			}
		}
	}

	if (typeof exports == "object") {
		module.exports = VueEditable;
	} else if (typeof define == "function" && define.amd) {
		define([], function() {
			return VueEditable;
		});
	} else if (window.Vue) {
		window.VueEditable = VueEditable;
		Vue.use(VueEditable);
	};
})();