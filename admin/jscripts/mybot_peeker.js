/**
 * Peeker controls the visibility of an element based on the value of an input
 *
 * @example
 *
 * var peeker = new Peeker($('#myController'), $('#myDomain'), /1/, false);
 * var peeker = new Peeker($('.myControllerNode'), $('#myDomain'), /1/, true);
 */

var Peeker = (function() {
	/**
	 * Constructor
	 *
	 * @param string ID of the controlling select menu
	 * @param string ID of the thing to show/hide
	 * @param regexp If this regexp matches value of the select menu, then the 'thing' will be shown
	 */
	function Peeker(controller, domain, match) {
		var fn;

		// verify input
		if (!controller || !domain) {
			return;
		}
		this.controller = controller;
		this.domain = domain;
		this.match = match;

		// create a context-bound copy of the function
		fn = $.proxy(this.check, this);

		this.controller.on('change', fn);

		this.check();
	}

	/**
	 * Checks the controller and shows/hide
	 *
	 * @return void
	 */
	function check() {
		var type = '', show = false, regex = this.match;

		this.controller.children(':selected').each(function(i, el) {
			if($(el).val().match(regex)) {
				show = true;
				return false;
			}
		});

		this.domain[show ? 'show' : 'hide']();
	}

	Peeker.prototype = {
		controller: null,
		domain: null,
		match: null,
		check: check,
	};

	return Peeker;
})();