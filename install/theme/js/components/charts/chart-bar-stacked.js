//
// Charts
//

'use strict';

//
// Doughnut chart
//

var BarStackedChart = (function() {

	// Variables

	var $chart = $('#chart-bar-stacked');


	// Methods

	function init($this) {

		// Only for demo purposes - return a random number to generate datasets
		var randomScalingFactor = function() {
			return Math.round(Math.random() * 100);
		};


		// Chart data

		var data = {
			labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
			datasets: [{
				label: 'Dataset 1',
				backgroundColor: Charts.colors.theme['danger'],
				data: [
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor()
				]
			}, {
				label: 'Dataset 2',
				backgroundColor: Charts.colors.theme['primary'],
				data: [
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor()
				]
			}, {
				label: 'Dataset 3',
				backgroundColor: Charts.colors.theme['success'],
				data: [
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor(),
					randomScalingFactor()
				]
			}]

		};


		// Options

		var options = {
			tooltips: {
				mode: 'index',
				intersect: false
			},
			responsive: true,
			scales: {
				xAxes: [{
					stacked: true,
				}],
				yAxes: [{
					stacked: true
				}]
			}
		}


		// Init chart

		var barStackedChart = new Chart($this, {
			type: 'bar',
			data: data,
			options: options
		});

		// Save to jQuery object

		$this.data('chart', barStackedChart);

	};


	// Events

	if ($chart.length) {
		init($chart);
	}

})();
