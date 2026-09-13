/* global window */
(function () {
	'use strict';
	function History(state) {
		this.state = state;
		this.entries = [state.snapshot()];
		this.index = 0;
	}
	History.prototype.record = function () {
		var snapshot = this.state.snapshot();
		var previous = JSON.stringify(this.entries[this.index]);
		if (previous === JSON.stringify(snapshot)) { return; }
		this.entries = this.entries.slice(0, this.index + 1);
		this.entries.push(snapshot);
		if (this.entries.length > 60) { this.entries.shift(); } else { this.index++; }
	};
	History.prototype.undo = function () {
		if (this.index < 1) { return false; }
		this.index--;
		this.state.setDocument(this.entries[this.index]);
		return true;
	};
	History.prototype.redo = function () {
		if (this.index >= this.entries.length - 1) { return false; }
		this.index++;
		this.state.setDocument(this.entries[this.index]);
		return true;
	};
	window.SpeedBuilderHistory = History;
}());
