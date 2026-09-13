/* global window */
(function () {
	'use strict';

	function clone(value) {
		return JSON.parse(JSON.stringify(value));
	}

	function makeId(prefix) {
		return 'sb_' + prefix + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
	}

	function defaultElement(type) {
		var settings = {};
		var styles = {};
		var children = [];
		if (type === 'heading') {
			settings = { text: 'A clear new heading', tag: 'h2' };
			styles = { color: '#162033', fontSize: '42px', fontWeight: '600', textAlign: 'left' };
		}
		if (type === 'text') {
			settings = { text: 'Add a thoughtful paragraph that gives your idea a little more room to breathe.' };
			styles = { color: '#667085', fontSize: '16px', lineHeight: '1.65', textAlign: 'left' };
		}
		if (type === 'button') {
			settings = { text: 'Get started', url: '#', newTab: false, alignment: 'left' };
			styles = { backgroundColor: '#155eef', color: '#ffffff', borderRadius: '6px', padding: '12px 18px', fontSize: '14px', fontWeight: '600' };
		}
		if (type === 'image') {
			settings = { attachmentId: 0, url: '', alt: '', link: '', alignment: 'left' };
		}
		if (type === 'spacer') {
			settings = { height: 48 };
		}
		if (type === 'divider') {
			settings = { style: 'solid', thickness: 1, width: 100, color: '#d0d5dd', alignment: 'left' };
		}
		if (['section', 'container', 'column'].indexOf(type) === -1) {
			children = undefined;
		}
		return { id: makeId(type), type: type, settings: settings, styles: styles, responsive: { desktop: {}, tablet: {}, mobile: {} }, children: children };
	}

	function makeSection(columnCount) {
		var section = defaultElement('section');
		section.children = [];
		for (var i = 0; i < columnCount; i++) {
			var column = defaultElement('column');
			column.children = [];
			section.children.push(column);
		}
		return section;
	}

	function findElement(nodes, id, parent) {
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].id === id) {
				return { element: nodes[i], parent: parent || null, index: i };
			}
			if (nodes[i].children) {
				var found = findElement(nodes[i].children, id, nodes[i]);
				if (found) { return found; }
			}
		}
		return null;
	}

	function findFirstDropTarget(nodes) {
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type === 'column' || nodes[i].type === 'container') { return nodes[i]; }
			if (nodes[i].children) {
				var target = findFirstDropTarget(nodes[i].children);
				if (target) { return target; }
			}
		}
		return null;
	}

	function SpeedBuilderState(document) {
		this.document = document || { version: '1.0', elements: [] };
		this.selectedId = null;
		this.device = 'desktop';
		this.listeners = [];
	}

	SpeedBuilderState.prototype.onChange = function (listener) { this.listeners.push(listener); };
	SpeedBuilderState.prototype.emit = function () { this.listeners.forEach(function (listener) { listener(); }); };
	SpeedBuilderState.prototype.setDocument = function (document) { this.document = clone(document); this.emit(); };
	SpeedBuilderState.prototype.snapshot = function () { return clone(this.document); };
	SpeedBuilderState.prototype.getSelected = function () { return this.selectedId ? findElement(this.document.elements, this.selectedId) : null; };
	SpeedBuilderState.prototype.select = function (id) { this.selectedId = id; this.emit(); };
	SpeedBuilderState.prototype.addSection = function (columns) { var section = makeSection(columns || 1); this.document.elements.push(section); this.selectedId = section.id; this.emit(); return section; };
	SpeedBuilderState.prototype.addElement = function (type, parentId) {
		var element = defaultElement(type);
		if (type === 'section') { return this.addSection(1); }
		if (type === 'columns') { return this.addSection(2); }
		if (type === 'container') {
			element.children = [];
			if (!parentId) { this.document.elements.push(element); } else { this.insertInto(parentId, element); }
		} else {
			var target = parentId ? findElement(this.document.elements, parentId) : this.getSelected();
			if (!target || ['column', 'container'].indexOf(target.element.type) === -1) {
				target = findFirstDropTarget(this.document.elements);
			}
			if (!target) {
				var section = this.addSection(1);
				target = findElement(this.document.elements, section.children[0].id);
			}
			target.element.children.push(element);
		}
		this.selectedId = element.id;
		this.emit();
		return element;
	};
	SpeedBuilderState.prototype.insertInto = function (parentId, element) {
		var parent = findElement(this.document.elements, parentId);
		if (parent && parent.element.children) { parent.element.children.push(element); return true; }
		return false;
	};
	SpeedBuilderState.prototype.updateSelected = function (path, value) {
		var selected = this.getSelected();
		if (!selected) { return; }
		var target = selected.element;
		for (var i = 0; i < path.length - 1; i++) { target[path[i]] = target[path[i]] || {}; target = target[path[i]]; }
		target[path[path.length - 1]] = value;
		this.emit();
	};
	SpeedBuilderState.prototype.removeSelected = function () {
		var selected = this.getSelected();
		if (!selected) { return; }
		var list = selected.parent ? selected.parent.children : this.document.elements;
		list.splice(selected.index, 1);
		this.selectedId = null;
		this.emit();
	};
	SpeedBuilderState.prototype.duplicateSelected = function () {
		var selected = this.getSelected();
		if (!selected) { return; }
		var duplicate = clone(selected.element);
		function refreshIds(element) { element.id = makeId(element.type); (element.children || []).forEach(refreshIds); }
		refreshIds(duplicate);
		var list = selected.parent ? selected.parent.children : this.document.elements;
		list.splice(selected.index + 1, 0, duplicate);
		this.selectedId = duplicate.id;
		this.emit();
	};
	SpeedBuilderState.prototype.moveSelected = function (direction) {
		var selected = this.getSelected();
		if (!selected) { return; }
		var list = selected.parent ? selected.parent.children : this.document.elements;
		var next = selected.index + direction;
		if (next < 0 || next >= list.length) { return; }
		var item = list.splice(selected.index, 1)[0];
		list.splice(next, 0, item);
		this.emit();
	};
	SpeedBuilderState.prototype.moveElement = function (id, targetParentId, targetIndex) {
		var found = findElement(this.document.elements, id);
		var target = findElement(this.document.elements, targetParentId);
		if (!found || !target || !target.element.children || found.element.id === target.element.id) { return; }
		var from = found.parent ? found.parent.children : this.document.elements;
		var element = from.splice(found.index, 1)[0];
		var index = typeof targetIndex === 'number' ? targetIndex : target.element.children.length;
		if (found.parent && found.parent.id === targetParentId && found.index < index) { index--; }
		target.element.children.splice(index, 0, element);
		this.selectedId = element.id;
		this.emit();
	};

	window.SpeedBuilderState = { State: SpeedBuilderState, clone: clone, findElement: findElement };
}());
