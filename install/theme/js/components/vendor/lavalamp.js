//
// Lavalamp
//

'use strict';

var Lavalamp = (function() {

	// Variables

	var $nav = $('[data-toggle="lavalamp"]');


	// Methods

	function init($this) {
		var options = {
			setOnClick: false,
	        enableHover: true,
	        margins: true,
	        autoUpdate: true,
	        duration: 200
		};

		$this.lavalamp(options);
	}


	// Events

	if ($nav.length) {
		$nav.each(function() {
			init($(this));
		});
	}

})();
