var WeatherStation = WeatherStation || {};

/**
 * localStorage with expire wrapper
 */
var myStorage = (function() {
	var self = {};

	/**
	 * Method unsets value in localStorage
	 */
	self.unset = function(key) {
		localStorage.removeItem(key);
	};

	/**
	 * Method gets value from localStorage
	 */
	self.get = function(key) {

		if (!localStorage[key]) {
			return null;
		}

		var object = JSON.parse(localStorage[key]);

		if (object.timestamp === null) {
			return object.value;
		}

		if (new Date().getTime() < object.timestamp) {
			return object.value;
		} else {
			return null;
		}

	};

	/**
	 * Method sets value in local storage
	 * 
	 * @param key
	 * @param value
	 * @param expire
	 *            in seconds
	 */
	self.set = function(key, value, expire) {

		var object;

		if (!expire) {
			object = {
				value : value,
				timestamp : null
			};
		} else {
			object = {
				value : value,
				timestamp : new Date().getTime() + (expire * 1000)
			};
		}

		localStorage[key] = JSON.stringify(object);
	};

	return self;
})();

WeatherStation.general = (function() {
	var self = {};

	self.notify = function() {

	};

	return self;
})();

WeatherStation.API = (function() {

	var self = {};

	self.getCurrent = function(onSuccess, onFailure) {

		var cache = myStorage.get('current-json');

		if (cache === null) {

			$
					.ajax({
						url : "http://api.openweathermap.org/data/2.5/weather?id=3083829&mode=json&units=metric",
						dataType : 'jsonp',
						success : function(json) {

							myStorage.set('current-json', json, 1800);

							if (onSuccess) {
								onSuccess(json);
							}

						},
						error : function() {
							if (onFailure) {
								onFailure();
							} else {
								$
										.pnotify({
											title : 'U la la...',
											text : 'Chyba nie udało mi się pobrać wszystkich danych o pogodzie',
											type : 'error'
										});
							}
						},
					});

		} else {
			onSuccess(cache);
		}

	};

	self.getForecast = function(onSuccess, onFailure) {

		var cache = myStorage.get('forecast5-json');

		if (cache === null) {

			$
					.ajax({
						url : "http://api.openweathermap.org/data/2.5/forecast/daily?id=3083829&cnt=5&mode=json&units=metric",
						dataType : 'jsonp',
						success : function(json) {

							myStorage.set('forecast5-json', json, 1800);

							if (onSuccess) {
								onSuccess(json);
							}

						},
						error : function() {
							if (onFailure) {
								onFailure();
							} else {
								$
										.pnotify({
											title : 'U la la...',
											text : 'Chyba nie udało mi się pobrać prognozy pogody...',
											type : 'error'
										});
							}
						},
					});

		} else {
			onSuccess(cache);
		}

	};

	self.getHistory = function(onSuccess, onFailure) {
		
		var cache = myStorage.get('history-json');

		if (cache === null) {
		
			var curd = new Date();
			var d = new Date(curd.getFullYear(), curd.getMonth(), curd.getDate());
			var s = Math.round((d.getTime()) / 1000) - 3600 * 24;
	
			$
					.ajax({
						url : "http://openweathermap.org/data/2.1/history/city/?id=3083829&cnt=80&mode=json&start="
								+ s,
						dataType : 'jsonp',
						success : function(json) {
	
							myStorage.set('history-json', json, 1800);
	
							if (onSuccess) {
								onSuccess(json);
							}
						},
						error : function() {
							$.pnotify({
								title : 'U la la...',
								text : 'Chyba nie udało mi się pobrać historii...',
								type : 'error'
							});
						},
					});
		}else {
			onSuccess(cache);
		}
	};
	
	return self;
})();

WeatherStation.overview = (function() {

	var self = {};

	self.renderCurrent = function(json) {

		console.log(json);

		$('#icon-current').attr(
				'src',
				'http://openweathermap.org/img/w/' + json['weather'][0]['icon']
						+ '.png');
		$('#icon-current').removeClass('hidden');

	};

	self.renderForecast = function(json) {

		console.log(json);

		/*
		 * Today
		 */
		$('#icon-today').attr(
				'src',
				'http://openweathermap.org/img/w/'
						+ json['list'][0]['weather'][0]['icon'] + '.png');
		$('#icon-today').removeClass('hidden');

		$('#today-temperature').html(json['list'][0]['temp']['day']);
		$('#today-humidity').html(json['list'][0]['humidity']);
		$('#today-pressure').html(parseInt(json['list'][0]['pressure']));
		$('#today-speed').html(parseInt(json['list'][0]['speed']));
		$('#today-direction').html(parseInt(json['list'][0]['deg']));

		/*
		 * Tomorrow
		 */

		$('#icon-tomorrow').attr(
				'src',
				'http://openweathermap.org/img/w/'
						+ json['list'][1]['weather'][0]['icon'] + '.png');
		$('#icon-tomorrow').removeClass('hidden');

		$('#tomorrow-temperature').html(json['list'][1]['temp']['day']);
		$('#tomorrow-humidity').html(json['list'][1]['humidity']);
		$('#tomorrow-pressure').html(parseInt(json['list'][1]['pressure'], 10));
		$('#tomorrow-speed').html(parseInt(json['list'][1]['speed']));
		$('#tomorrow-direction').html(parseInt(json['list'][1]['deg']));
	};

	self.windRose = function(json) {
		showPolarSpeed('chart-wind', json.list);
	};
	
	/**
	 * Constructor
	 */
	init = function() {
		WeatherStation.API.getCurrent(self.renderCurrent);
		WeatherStation.API.getForecast(self.renderForecast);
		WeatherStation.API.getHistory(self.windRose);
	};

	init();

	return self;

})();

WeatherStation.overview;