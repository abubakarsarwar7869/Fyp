/* global window */
(function () {
	'use strict';
	function DragDrop(editor) {
		this.editor = editor;
		this.draggedId = null;
	}
	DragDrop.prototype.bind = function () {
		var self = this;
		document.addEventListener('dragstart', function (event) {
			var palette = event.target.closest('[data-sb-widget]');
			var node = event.target.closest('[data-sb-node-id]');
			if (palette) {
				event.dataTransfer.setData('speed-builder/widget', palette.getAttribute('data-sb-widget'));
				event.dataTransfer.effectAllowed = 'copy';
			}
			if (node && !event.target.closest('[contenteditable="true"]')) {
				self.draggedId = node.getAttribute('data-sb-node-id');
				event.dataTransfer.setData('speed-builder/node', self.draggedId);
				event.dataTransfer.effectAllowed = 'move';
			}
		});
		document.addEventListener('dragover', function (event) {
			var zone = event.target.closest('[data-sb-drop-id]');
			if (zone) { event.preventDefault(); zone.classList.add('is-drag-over'); }
		});
		document.addEventListener('dragleave', function (event) {
			var zone = event.target.closest('[data-sb-drop-id]');
			if (zone) { zone.classList.remove('is-drag-over'); }
		});
		document.addEventListener('drop', function (event) {
			var zone = event.target.closest('[data-sb-drop-id]');
			if (!zone) { return; }
			event.preventDefault();
			zone.classList.remove('is-drag-over');
			var parentId = zone.getAttribute('data-sb-drop-id');
			var type = event.dataTransfer.getData('speed-builder/widget');
			var nodeId = event.dataTransfer.getData('speed-builder/node');
			if (type) { self.editor.mutate(function () { self.editor.state.addElement(type, parentId); }); }
			if (nodeId) { self.editor.mutate(function () { self.editor.state.moveElement(nodeId, parentId); }); }
			self.draggedId = null;
		});
	};
	window.SpeedBuilderDragDrop = DragDrop;
}());
