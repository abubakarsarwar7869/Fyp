/* global SpeedBuilderConfig, SpeedBuilderState, SpeedBuilderHistory, SpeedBuilderResponsive, SpeedBuilderDragDrop, wp */
(function () {
	'use strict';

	function escapeHtml(value) {
		return String(value || '').replace(/[&<>'"]/g, function (character) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character];
		});
	}

	function css(value) { return String(value || '').replace(/[;{}]/g, ''); }

	function Editor() {
		this.canvas = document.getElementById('sb-canvas');
		this.settings = document.getElementById('sb-settings-content');
		this.settingsTitle = document.getElementById('sb-settings-title');
		this.toolbar = document.getElementById('sb-floating-toolbar');
		this.toastNode = document.getElementById('sb-toast');
		this.sectionPicker = document.getElementById('sb-section-picker');
		this.state = null;
		this.history = null;
		this.responsive = null;
		this.autosaveTimer = null;
		this.activeTab = 'content';
		this.zoom = 100;
	}

	Editor.prototype.start = function () {
		var self = this;
		fetch(SpeedBuilderConfig.restUrl + 'pages/' + SpeedBuilderConfig.postId + '/document', { credentials: 'same-origin', headers: { 'X-WP-Nonce': SpeedBuilderConfig.nonce } })
			.then(function (response) { if (!response.ok) { throw new Error('Unable to load document'); } return response.json(); })
			.then(function (response) {
				self.state = new SpeedBuilderState.State(response.document);
				self.history = new SpeedBuilderHistory(self.state);
				self.responsive = new SpeedBuilderResponsive(self.state);
				self.state.onChange(function () { self.render(); });
				new SpeedBuilderDragDrop(self).bind();
				self.bindEvents();
				self.render();
				document.getElementById('sb-editor-loading').classList.add('is-hidden');
			})
			.catch(function () { self.showToast('Unable to load the editor. Refresh and try again.', true); });
	};

	Editor.prototype.mutate = function (callback, autosave) {
		callback();
		this.history.record();
		if (autosave !== false) { this.scheduleAutosave(); }
	};

	Editor.prototype.scheduleAutosave = function () {
		var self = this;
		clearTimeout(this.autosaveTimer);
		this.autosaveTimer = setTimeout(function () { self.save(true); }, Math.max(750, parseInt(SpeedBuilderConfig.autosaveInterval, 10) || 1500));
	};

	Editor.prototype.save = function (quiet) {
		var self = this;
		if (!this.state) { return; }
		fetch(SpeedBuilderConfig.restUrl + 'pages/' + SpeedBuilderConfig.postId + '/document', {
			method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': SpeedBuilderConfig.nonce },
			body: JSON.stringify({ document: this.state.document })
		}).then(function (response) {
			if (!response.ok) { throw new Error('Save failed'); }
			return response.json();
		}).then(function () {
			if (!quiet) { self.showToast('Saved'); }
			else { self.setSaveState('Saved'); }
		}).catch(function () { self.showToast('Unable to save your changes.', true); });
	};

	Editor.prototype.publish = function () {
		var self = this;
		this.save(true);
		fetch(SpeedBuilderConfig.restUrl + 'pages/' + SpeedBuilderConfig.postId + '/publish', { method: 'POST', credentials: 'same-origin', headers: { 'X-WP-Nonce': SpeedBuilderConfig.nonce } })
			.then(function (response) { if (!response.ok) { throw new Error('Publish failed'); } return response.json(); })
			.then(function () { self.showToast('Page published successfully.'); })
			.catch(function () { self.showToast('Unable to publish this page.', true); });
	};

	Editor.prototype.setSaveState = function (text) {
		var save = document.querySelector('[data-sb-action="save"]');
		if (!save) { return; }
		save.textContent = text;
		setTimeout(function () { save.textContent = 'Save'; }, 1200);
	};

	Editor.prototype.showToast = function (message, error) {
		var self = this;
		this.toastNode.textContent = message;
		this.toastNode.className = 'sb-toast is-visible' + (error ? ' is-error' : '');
		clearTimeout(this.toastTimer);
		this.toastTimer = setTimeout(function () { self.toastNode.className = 'sb-toast'; }, 2600);
	};

	Editor.prototype.nodeClass = function (element) {
		return 'sb-node sb-node-' + element.type + (this.state.selectedId === element.id ? ' is-selected' : '');
	};

	Editor.prototype.nodeStart = function (element, tag) {
		return '<' + (tag || 'div') + ' class="' + this.nodeClass(element) + '" data-sb-node-id="' + escapeHtml(element.id) + '" draggable="true">';
	};

	Editor.prototype.elementStyle = function (element) {
		var styles = Object.assign({}, element.styles || {});
		if (this.state.device !== 'desktop' && element.responsive && element.responsive[this.state.device]) {
			Object.assign(styles, element.responsive[this.state.device]);
		}
		var map = { color: 'color', backgroundColor: 'background-color', fontSize: 'font-size', fontWeight: 'font-weight', textAlign: 'text-align', lineHeight: 'line-height', margin: 'margin', padding: 'padding', borderRadius: 'border-radius', width: 'width' };
		var output = [];
		Object.keys(map).forEach(function (key) { if (styles[key]) { output.push(map[key] + ':' + css(styles[key])); } });
		return output.join(';');
	};

	Editor.prototype.render = function () {
		if (!this.state) { return; }
		this.canvas.className = 'sb-canvas sb-device-' + this.state.device;
		this.canvas.style.setProperty('--sb-zoom', this.zoom / 100);
		document.querySelectorAll('[data-sb-device]').forEach(function (button) {
			button.classList.toggle('is-active', button.getAttribute('data-sb-device') === this.state.device);
		}, this);
		if (!this.state.document.elements.length) {
			this.canvas.innerHTML = '<div class="sb-empty-canvas"><span>+</span><h1>Start building</h1><p>Create your first section, then drop in the elements that tell your story.</p><button data-sb-action="add-section">+ Add section</button></div>';
		} else {
			this.canvas.innerHTML = this.state.document.elements.map(this.renderElement.bind(this)).join('') + '<button class="sb-add-section-line" data-sb-action="add-section">+ Add section</button>';
		}
		this.renderSettings();
		this.toolbar.hidden = !this.state.selectedId;
	};

	Editor.prototype.renderElement = function (element) {
		var settings = element.settings || {};
		var style = this.elementStyle(element);
		var children = element.children || [];
		if (element.type === 'section') {
			return this.nodeStart(element, 'section') + '<span class="sb-node-label">Section</span><div class="sb-section-columns sb-columns-' + Math.min(4, Math.max(1, children.length)) + '">' + children.map(this.renderElement.bind(this)).join('') + '</div></section>';
		}
		if (element.type === 'column' || element.type === 'container') {
			var typeName = element.type === 'column' ? 'Column' : 'Container';
			return this.nodeStart(element) + '<span class="sb-node-label">' + typeName + '</span><div class="sb-dropzone" data-sb-drop-id="' + escapeHtml(element.id) + '">' + (children.length ? children.map(this.renderElement.bind(this)).join('') : '<button class="sb-empty-drop" data-sb-action="add-widget-here" data-sb-parent-id="' + escapeHtml(element.id) + '">+ Drop widgets here</button>') + '</div></div>';
		}
		if (element.type === 'heading') {
			var tag = ['h1','h2','h3','h4','h5','h6'].indexOf(settings.tag) !== -1 ? settings.tag : 'h2';
			return this.nodeStart(element) + '<span class="sb-node-label">Heading</span><' + tag + ' class="sb-inline-edit" data-sb-inline="text" style="' + style + '">' + escapeHtml(settings.text) + '</' + tag + '></div>';
		}
		if (element.type === 'text') {
			return this.nodeStart(element) + '<span class="sb-node-label">Text</span><div class="sb-inline-edit sb-text-output" data-sb-inline="text" style="' + style + '">' + escapeHtml(settings.text).replace(/\n/g, '<br>') + '</div></div>';
		}
		if (element.type === 'button') {
			return this.nodeStart(element) + '<span class="sb-node-label">Button</span><div class="sb-button-align sb-align-' + escapeHtml(settings.alignment || 'left') + '"><span class="sb-canvas-button" style="' + style + '">' + escapeHtml(settings.text || 'Get started') + '</span></div></div>';
		}
		if (element.type === 'image') {
			var image = settings.url ? '<img src="' + escapeHtml(settings.url) + '" alt="' + escapeHtml(settings.alt) + '" style="' + style + '">' : '<button class="sb-image-placeholder" data-sb-action="choose-image">▧ <span>Choose image</span></button>';
			return this.nodeStart(element) + '<span class="sb-node-label">Image</span><div class="sb-image-align sb-align-' + escapeHtml(settings.alignment || 'left') + '">' + image + '</div></div>';
		}
		if (element.type === 'spacer') {
			return this.nodeStart(element) + '<span class="sb-node-label">Spacer · ' + parseInt(settings.height || 48, 10) + 'px</span><div class="sb-spacer-guide" style="height:' + parseInt(settings.height || 48, 10) + 'px"></div></div>';
		}
		if (element.type === 'divider') {
			return this.nodeStart(element) + '<span class="sb-node-label">Divider</span><div class="sb-divider-align sb-align-' + escapeHtml(settings.alignment || 'left') + '"><hr style="border-top:' + parseInt(settings.thickness || 1, 10) + 'px ' + escapeHtml(settings.style || 'solid') + ' ' + escapeHtml(settings.color || '#d0d5dd') + ';width:' + parseInt(settings.width || 100, 10) + '%"></div></div>';
		}
		return '';
	};

	Editor.prototype.renderSettings = function () {
		var found = this.state.getSelected();
		if (!found) {
			this.settingsTitle.textContent = 'Element settings';
			this.settings.innerHTML = '<div class="sb-no-selection"><span>⌖</span><b>Select an element</b><p>Choose something on the canvas to start editing it.</p></div>';
			return;
		}
		var element = found.element;
		this.settingsTitle.textContent = this.prettyType(element.type) + ' settings';
		this.settings.innerHTML = this.renderTabs() + this.renderSettingsTab(element);
	};

	Editor.prototype.renderTabs = function () {
		return '<div class="sb-mobile-device-note">Editing for <b>' + this.state.device + '</b></div>';
	};

	Editor.prototype.prettyType = function (type) { return { heading: 'Heading', text: 'Text', button: 'Button', image: 'Image', spacer: 'Spacer', divider: 'Divider', section: 'Section', column: 'Column', container: 'Container' }[type] || type; };
	Editor.prototype.field = function (label, name, value, type, extra) {
		var input = type === 'textarea' ? '<textarea data-sb-setting="' + name + '">' + escapeHtml(value) + '</textarea>' : '<input type="' + (type || 'text') + '" data-sb-setting="' + name + '" value="' + escapeHtml(value) + '" ' + (extra || '') + '>';
		return '<label class="sb-control"><span>' + label + '</span>' + input + '</label>';
	};
	Editor.prototype.select = function (label, name, value, choices) {
		return '<label class="sb-control"><span>' + label + '</span><select data-sb-setting="' + name + '">' + choices.map(function (choice) { return '<option value="' + choice[0] + '"' + (String(value) === choice[0] ? ' selected' : '') + '>' + choice[1] + '</option>'; }).join('') + '</select></label>';
	};

	Editor.prototype.renderSettingsTab = function (element) {
		var s = element.settings || {}, style = element.styles || {}, responsive = element.responsive || {}, deviceStyle = this.state.device === 'desktop' ? style : Object.assign({}, style, responsive[this.state.device] || {});
		if (this.activeTab === 'content') {
			if (element.type === 'heading') { return this.field('Text', 'text', s.text, 'textarea') + this.select('HTML tag', 'tag', s.tag, [['h1','H1'],['h2','H2'],['h3','H3'],['h4','H4'],['h5','H5'],['h6','H6']]); }
			if (element.type === 'text') { return this.field('Text', 'text', s.text, 'textarea') + '<p class="sb-control-hint">Use simple text here. Line breaks are preserved in the page.</p>'; }
			if (element.type === 'button') { return this.field('Text', 'text', s.text) + this.field('URL', 'url', s.url, 'url') + '<label class="sb-check"><input type="checkbox" data-sb-setting="newTab"' + (s.newTab ? ' checked' : '') + '> Open in new tab</label>' + this.select('Alignment', 'alignment', s.alignment, [['left','Left'],['center','Center'],['right','Right']]); }
			if (element.type === 'image') { return '<button class="sb-choose-image" data-sb-action="choose-image">▧ <b>Choose image</b><small>Use your WordPress Media Library</small></button>' + this.field('Image URL', 'url', s.url, 'url') + this.field('Alt text', 'alt', s.alt) + this.field('Link', 'link', s.link, 'url') + this.select('Alignment', 'alignment', s.alignment, [['left','Left'],['center','Center'],['right','Right']]); }
			if (element.type === 'spacer') { return this.field('Height', 'height', s.height, 'number', 'min="0" max="800"') + '<p class="sb-control-hint">The height can be adjusted per device in the responsive mode.</p>'; }
			if (element.type === 'divider') { return this.select('Style', 'style', s.style, [['solid','Solid'],['dashed','Dashed'],['dotted','Dotted']]) + this.field('Thickness', 'thickness', s.thickness, 'number', 'min="1" max="20"') + this.field('Width', 'width', s.width, 'number', 'min="1" max="100"') + this.field('Color', 'color', s.color, 'color') + this.select('Alignment', 'alignment', s.alignment, [['left','Left'],['center','Center'],['right','Right']]); }
			return '<div class="sb-layout-settings"><span class="sb-layout-icon">' + (element.type === 'section' ? '▤' : '□') + '</span><b>' + this.prettyType(element.type) + '</b><p>Drag widgets into this area or use the Elements panel to add content.</p></div>';
		}
		if (this.activeTab === 'style') {
			return '<div class="sb-device-switch"><span>Editing for</span><b>' + this.state.device + '</b></div>' + this.field('Text color', 'style:color', deviceStyle.color || '#162033', 'color') + this.field('Font size', 'style:fontSize', deviceStyle.fontSize || (element.type === 'heading' ? '42px' : '16px')) + this.select('Font weight', 'style:fontWeight', deviceStyle.fontWeight || '400', [['400','Regular'],['500','Medium'],['600','Semibold'],['700','Bold']]) + this.select('Text alignment', 'style:textAlign', deviceStyle.textAlign || 'left', [['left','Left'],['center','Center'],['right','Right']]) + this.field('Line height', 'style:lineHeight', deviceStyle.lineHeight || '1.5') + this.field('Background color', 'style:backgroundColor', deviceStyle.backgroundColor || '#ffffff', 'color') + this.field('Padding', 'style:padding', deviceStyle.padding || '') + this.field('Margin', 'style:margin', deviceStyle.margin || '') + this.field('Border radius', 'style:borderRadius', deviceStyle.borderRadius || '');
		}
		return this.field('CSS ID', 'advanced:id', element.id) + this.field('CSS class', 'advanced:class', '') + '<p class="sb-control-hint">CSS ID is generated safely for this element. Custom class support is reserved for a later phase.</p>';
	};

	Editor.prototype.updateFromSetting = function (input) {
		var name = input.getAttribute('data-sb-setting');
		if (!name || !this.state.getSelected()) { return; }
		var value = input.type === 'checkbox' ? input.checked : input.value;
		var self = this;
		this.mutate(function () {
			if (name.indexOf('style:') === 0) { self.responsive.updateStyle(name.replace('style:', ''), value); }
			else if (name.indexOf('advanced:') === 0) { return; }
			else { self.state.updateSelected(['settings', name], value); }
		});
	};

	Editor.prototype.chooseImage = function () {
		var self = this;
		if (!window.wp || !wp.media) { this.showToast('The WordPress media library is unavailable.', true); return; }
		var frame = wp.media({ title: 'Choose image', button: { text: 'Use this image' }, multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			var media = frame.state().get('selection').first().toJSON();
			self.mutate(function () {
				self.state.updateSelected(['settings', 'attachmentId'], media.id || 0);
				self.state.updateSelected(['settings', 'url'], media.url || '');
				self.state.updateSelected(['settings', 'alt'], media.alt || '');
			});
		});
		frame.open();
	};

	Editor.prototype.saveTemplate = function () {
		var self = this;
		var name = window.prompt('Name this template');
		if (!name) { return; }
		fetch(SpeedBuilderConfig.restUrl + 'templates', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': SpeedBuilderConfig.nonce }, body: JSON.stringify({ name: name, document: this.state.document }) })
			.then(function (response) { if (!response.ok) { throw new Error('Template failed'); } return response.json(); })
			.then(function () { self.showToast('Template saved to your library.'); })
			.catch(function () { self.showToast('Unable to save this template.', true); });
	};

	Editor.prototype.bindEvents = function () {
		var self = this;
		document.addEventListener('click', function (event) {
			var device = event.target.closest('[data-sb-device]');
			if (device) { self.responsive.setDevice(device.getAttribute('data-sb-device')); return; }
			var tab = event.target.closest('[data-sb-tab]');
			if (tab) { self.activeTab = tab.getAttribute('data-sb-tab'); document.querySelectorAll('[data-sb-tab]').forEach(function (button) { button.classList.toggle('is-active', button === tab); }); self.renderSettings(); return; }
			var widget = event.target.closest('[data-sb-widget]');
			if (widget) { self.mutate(function () { self.state.addElement(widget.getAttribute('data-sb-widget')); }); return; }
			var node = event.target.closest('[data-sb-node-id]');
			if (node && !event.target.closest('[data-sb-action]')) { self.state.select(node.getAttribute('data-sb-node-id')); return; }
			var action = event.target.closest('[data-sb-action]');
			if (!action) { return; }
			var command = action.getAttribute('data-sb-action');
			if (command === 'add-section') { self.sectionPicker.hidden = false; }
			if (command === 'close-section-picker') { self.sectionPicker.hidden = true; }
			if (command === 'add-widget-here') { self.state.select(action.getAttribute('data-sb-parent-id')); self.showToast('Choose a widget from the Elements panel.'); }
			if (command === 'undo') { if (self.history.undo()) { self.showToast('Undo'); self.scheduleAutosave(); } }
			if (command === 'redo') { if (self.history.redo()) { self.showToast('Redo'); self.scheduleAutosave(); } }
			if (command === 'save') { self.save(false); }
			if (command === 'publish') { self.publish(); }
			if (command === 'preview') { window.open(SpeedBuilderConfig.previewUrl, '_blank', 'noopener'); }
			if (command === 'save-template') { self.saveTemplate(); }
			if (command === 'delete') { self.mutate(function () { self.state.removeSelected(); }); }
			if (command === 'duplicate') { self.mutate(function () { self.state.duplicateSelected(); }); }
			if (command === 'move-up') { self.mutate(function () { self.state.moveSelected(-1); }); }
			if (command === 'move-down') { self.mutate(function () { self.state.moveSelected(1); }); }
			if (command === 'edit') { self.activeTab = 'content'; self.renderSettings(); }
			if (command === 'choose-image') { self.chooseImage(); }
			if (command === 'zoom-in') { self.zoom = Math.min(120, self.zoom + 10); self.render(); }
			if (command === 'zoom-out') { self.zoom = Math.max(70, self.zoom - 10); self.render(); }
		});
		document.addEventListener('click', function (event) {
			var option = event.target.closest('[data-sb-columns]');
			if (option) { self.mutate(function () { self.state.addSection(parseInt(option.getAttribute('data-sb-columns'), 10)); }); self.sectionPicker.hidden = true; }
		});
		document.addEventListener('change', function (event) { var input = event.target.closest('[data-sb-setting]'); if (input) { self.updateFromSetting(input); } });
		document.addEventListener('dblclick', function (event) {
			var editable = event.target.closest('[data-sb-inline]');
			if (!editable) { return; }
			editable.contentEditable = 'true'; editable.focus();
		});
		document.addEventListener('blur', function (event) {
			var editable = event.target.closest('[data-sb-inline]');
			if (!editable || editable.contentEditable !== 'true') { return; }
			var parent = editable.closest('[data-sb-node-id]');
			var text = editable.innerText;
			editable.contentEditable = 'false';
			self.state.select(parent.getAttribute('data-sb-node-id'));
			self.mutate(function () { self.state.updateSelected(['settings', 'text'], text); });
		}, true);
		document.addEventListener('input', function (event) {
			var search = event.target.closest('[data-sb-search]');
			if (search) { var term = search.value.toLowerCase(); document.querySelectorAll('.sb-widget-button').forEach(function (widget) { widget.hidden = widget.textContent.toLowerCase().indexOf(term) === -1; }); }
		});
		document.addEventListener('keydown', function (event) {
			var typing = ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(document.activeElement.tagName) !== -1 || document.activeElement.isContentEditable;
			if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 's') { event.preventDefault(); self.save(false); }
			if (!typing && (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'z') { event.preventDefault(); event.shiftKey ? self.history.redo() : self.history.undo(); self.scheduleAutosave(); }
			if (!typing && (event.key === 'Delete' || event.key === 'Backspace') && self.state.selectedId) { event.preventDefault(); self.mutate(function () { self.state.removeSelected(); }); }
			if (event.key === 'Escape') { self.state.select(null); self.sectionPicker.hidden = true; }
		});
	};

	function boot() { new Editor().start(); }
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
}());
