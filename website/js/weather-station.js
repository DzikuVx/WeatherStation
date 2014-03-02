function pad(a,b){
	return(1e15+a+"").slice(-b);
}

var WeatherStation = WeatherStation || {};

WeatherStation.general = (function() {
	var self = {};

	self.notify = function() {

	};

	return self;
})();

WeatherStation.API = (function() {

	var self = {};

	var process = function (data, onSuccess, onFailure) {
		if (!data) {
            if (onFailure) {
			    onFailure();
            }
		} else {
            if (onSuccess) {
                onSuccess(data);
            }
        }
	};
	
	self.getCurrent = function(onSuccess, onFailure) {
		process(weatherProxy.current, onSuccess, onFailure);
	};

	self.getForecast = function(onSuccess, onFailure) {
		process(weatherProxy.forecast, onSuccess, onFailure);
	};

	return self;
})();

WeatherStation.overview = (function() {

	var self = {};

	self.renderCurrent = function(json) {
        /** @namespace json.weather */
        $('#icon-current').attr('src','http://openweathermap.org/img/w/' + json.weather[0].icon + '.png').removeClass('hidden');
	};

	self.renderOverview = function(json) {
		/*
		 * Today
		 */
        $('#icon-today').attr('src', 'http://openweathermap.org/img/w/' + json.list[0].weather[0].icon + '.png').removeClass('hidden');
		$('#today-temperature').html(json.list[0].temp.day);
		$('#today-humidity').html(json.list[0].humidity);
		$('#today-pressure').html(parseInt(json.list[0].pressure, 10));
		$('#today-speed').html(parseInt(json.list[0].speed, 10));
		$('#today-direction').html(parseInt(json.list[0].deg, 10));

		/*
		 * Tomorrow
		 */
		$('#icon-tomorrow').attr('src', 'http://openweathermap.org/img/w/' + json.list[1].weather[0].icon + '.png').removeClass('hidden');
		$('#tomorrow-temperature').html(json.list[1].temp.day);
		$('#tomorrow-humidity').html(json.list[1].humidity);
		$('#tomorrow-pressure').html(parseInt(json.list[1].pressure, 10));
		$('#tomorrow-speed').html(parseInt(json.list[1].speed, 10));
		$('#tomorrow-direction').html(parseInt(json.list[1].deg, 10));
	};

	self.renderForecast = function(json) {
		var $template = $('#forecast-template'),
            template = $template.html(),
            $rowTemplate = $('#row-template'),
            rowTemplate = $rowTemplate.html(),
			rowCount = Math.ceil(json.cnt / 3),
            $container = $('#container'),
			rowNumber,
			i,
			currentElement,
			date;
		
		/*
		 * Remove templates as unneeded anymore
		 */
        $template.remove();
        $rowTemplate.remove();

		for (i = 0; i < rowCount; i++) {
            $container.append(rowTemplate);
            $container.find('.overview-row').last().attr('id', 'row-' + (i+1));
		}

        /** @namespace json.cnt */
        for (i = 0; i < json.cnt; i++) {
			
			rowNumber = Math.ceil((i+1) / 3);
			
			$('#row-' + rowNumber).append(template);

			currentElement = $('.overview-box').last();
			
			currentElement.find('[data-type=icon]').attr(
					'src',
					'http://openweathermap.org/img/w/' + json.list[i].weather[0].icon + '.png');
			
			currentElement.find('[data-type=temperature]').html(json.list[i].temp.day);
			currentElement.find('[data-type=humidity]').html(json.list[i].humidity);
			currentElement.find('[data-type=pressure]').html(json.list[i].pressure);
			currentElement.find('[data-type=speed]').html(json.list[i].speed);
			currentElement.find('[data-type=direction]').html(json.list[i].deg);
			
			date = new Date(json.list[i].dt * 1000);
			currentElement.find('[data-type=date]').html(date.getFullYear() + '-' + pad((date.getMonth()+1),2) + '-' + pad(date.getDate(), 2));
			
		}
		
	};
	
	self.onError = function () {
		$.pnotify({
			title : 'U la la...',
			text : 'Chyba nie udało mi się pobrać wszystkich danych o pogodzie',
			type : 'error'
		});
	};
	
	/**
	 * Constructor
	 */
	this.init = function() {
		
		var process = $('process');
		
		if (process.length != 1) {
			return false;
		}
		
		if (process.attr('data-type') === 'overview') {
			WeatherStation.API.getCurrent(self.renderCurrent, self.onError);
			WeatherStation.API.getForecast(self.renderOverview, self.onError);
		}else if (process.attr('data-type') === 'forecast') {
			WeatherStation.API.getForecast(self.renderForecast);
		}

		return true;
		
	};

	this.init();

	return self;

})();

var weather = WeatherStation.overview;
