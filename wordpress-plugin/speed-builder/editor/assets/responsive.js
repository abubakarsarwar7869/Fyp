/* global window */
(function () {
	'use strict';
	function Responsive(state) { this.state = state; }
	Responsive.prototype.setDevice = function (device) {
		if (['desktop', 'tablet', 'mobile'].indexOf(device) === -1) { return; }
		this.state.device = device;
		this.state.emit();
	};
	Responsive.prototype.getStyle = function (element, property) {
		if (this.state.device !== 'desktop' && element.responsive && element.responsive[this.state.device] && element.responsive[this.state.device][property] !== undefined) {
			return element.responsive[this.state.device][property];
		}
		return (element.styles || {})[property] || '';
	};
	Responsive.prototype.updateStyle = function (property, value) {
		var path = this.state.device === 'desktop' ? ['styles', property] : ['responsive', this.state.device, property];
		this.state.updateSelected(path, value);
	};
	window.SpeedBuilderResponsive = Responsive;
}());
