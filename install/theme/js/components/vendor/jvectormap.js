//
// Quill.js
//

'use strict';

var VectorMap = (function() {

	// Variables

	var $vectormap = $('[data-toggle="vectormap"]'),
		colors = {
			gray: {
				100: '#f6f9fc',
				200: '#e9ecef',
				300: '#dee2e6',
				400: '#ced4da',
				500: '#adb5bd',
				600: '#8898aa',
				700: '#525f7f',
				800: '#32325d',
				900: '#212529'
			},
			theme: {
				'default': '#172b4d',
				'primary': '#5e72e4',
				'secondary': '#f4f5f7',
				'info': '#11cdef',
				'success': '#2dce89',
				'danger': '#f5365c',
				'warning': '#fb6340'
			},
			black: '#12263F',
			white: '#FFFFFF',
			transparent: 'transparent',
		};

	// Methods

	function init($this) {

		// Get placeholder
		var map = $this.data('map'),

            series = {
                "AU": 760,
                "BR": 550,
                "CA": 120,
                "DE": 1300,
                "FR": 540,
                "GB": 690,
                "GE": 200,
                "IN": 200,
                "RO": 600,
                "RU": 300,
                "US": 2920,
            },

			options = {
				map: map,
                zoomOnScroll: false,
				scaleColors: ['#f00', '#0071A4'],
				normalizeFunction: 'polynomial',
				hoverOpacity: 0.7,
				hoverColor: false,
                backgroundColor: colors.transparent,
                regionStyle: {
                    initial: {
                        fill: colors.gray[200],
                        "fill-opacity": .8,
                        stroke: 'none',
                        "stroke-width": 0,
                        "stroke-opacity": 1
                    },
                    hover: {
                        fill: colors.gray[300],
                        "fill-opacity": .8,
                        cursor: 'pointer'
                    },
                    selected: {
                        fill: 'yellow'
                    },
                        selectedHover: {
                    }
                },
                markerStyle: {
					initial: {
						fill: colors.theme.warning,
                        "stroke-width": 0
					},
					hover: {
						fill: colors.theme.info,
                        "stroke-width": 0
					},
				},
				markers: [
                    {
						latLng: [41.90, 12.45],
						name: 'Vatican City'
					},
					{
						latLng: [43.73, 7.41],
						name: 'Monaco'
					},
					{
						latLng: [35.88, 14.5],
						name: 'Malta'
					},
					{
						latLng: [1.3, 103.8],
						name: 'Singapore'
					},
					{
						latLng: [1.46, 173.03],
						name: 'Kiribati'
					},
					{
						latLng: [-21.13, -175.2],
						name: 'Tonga'
					},
					{
						latLng: [15.3, -61.38],
						name: 'Dominica'
					},
					{
						latLng: [-20.2, 57.5],
						name: 'Mauritius'
					},
					{
						latLng: [26.02, 50.55],
						name: 'Bahrain'
					}
				],
                series: {
                    regions: [{
                        values: series,
                        scale: [colors.gray[400], colors.gray[500]],
                        normalizeFunction: 'polynomial'
                    }]
                },
			};

		// Init map
		$this.vectorMap(options);

		// Customize controls
		$this.find('.jvectormap-zoomin').addClass('btn btn-sm btn-primary');
		$this.find('.jvectormap-zoomout').addClass('btn btn-sm btn-primary');

	}

	// Events

	if ($vectormap.length) {
		$vectormap.each(function() {
			init($(this));
		});
	}

})();
