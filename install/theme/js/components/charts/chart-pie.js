//
// Charts
//

'use strict';

//
// Doughnut chart
//

var PieChart = (function() {

	// Variables

	var $chart = $('#chart-pie');


	// Methods

	function init($this) {
		var randomScalingFactor = function() {
			return Math.round(Math.random() * 100);
		};

		var pieChart = new Chart($this, {
			type: 'pie',
			data: {
				labels: [
					'Danger',
					'Warning',
					'Success',
					'Primary',
					'Info'
				],
				datasets: [{
					data: [
						randomScalingFactor(),
						randomScalingFactor(),
						randomScalingFactor(),
						randomScalingFactor(),
						randomScalingFactor(),
					],
					backgroundColor: [
						Charts.colors.theme['danger'],
						Charts.colors.theme['warning'],
						Charts.colors.theme['success'],
						Charts.colors.theme['primary'],
						Charts.colors.theme['info'],
					],
					label: 'Dataset 1'
				}],
			},
			options: {
				responsive: true,
				legend: {
					position: 'top',
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		});

		// Save to jQuery object

		$this.data('chart', pieChart);

	};


	// Events

	if ($chart.length) {
		init($chart);
	}

})();
