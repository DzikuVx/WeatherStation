var FormValidators = function() {

};

/**
 * Maska pól
 * 
 * @param object
 * @param type
 */
FormValidators.mask = function(object, type) {

	var original = '';
	var str = '';

	original = $(object).val();
	str = original;

	if (type == 'number' || type == 0) {
		var valid = "0123456789";
		var temp;
		var new_str;
		new_str = "";

		for ( var i = 0; i < str.length; i++) {
			temp = "" + str.substring(i, i + 1);
			if (valid.indexOf(temp) != "-1") {
				new_str = new_str + temp;
			}
		}

		str = new_str;
	}

	if (type == 'binumber' || type == 1) {
		var valid = ",0123456789";
		var temp;
		var new_str;
		new_str = "";

		for ( var i = 0; i < str.length; i++) {
			temp = "" + str.substring(i, i + 1);
			if (valid.indexOf(temp) != "-1") {
				new_str = new_str + temp;
			}
		}

		var dotPoint = FormValidators.strpos(new_str, ',',0);
		
		if (dotPoint) {
			new_str = new_str.slice(0,dotPoint+3);
		}
		
		str = new_str;
	}

	if ($(object).attr('size')) {
		
		if (str.length > $(object).attr('size')) {
			str = str.substring(0, $(object).attr('size'));
		}
		
	}
	
	if ($(object).attr('max')) {
		
		if (parseInt(str) > parseInt($(object).attr('max'))) {
			str = $(object).attr('max');
		}
		
	}
	
	if ($(object).attr('min')) {

		if (parseInt(str) < parseInt($(object).attr('min'))) {
			str = $(object).attr('min');
		}

	}

	if (original != str) {
		$(object).val(str);
	}

};

FormValidators.strpos = function (haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));
    return i === -1 ? false : i;
};