/**
 * The repeater behind the itinerary, FAQ and list fields.
 *
 * Rows are cloned from a <template> the PHP already rendered, so the markup
 * for a new row and a saved one can never drift apart. Group rows carry an
 * index inside their input names (foo[0][title]); the index only has to be
 * unique within the form, not contiguous, because PHP re-indexes on save.
 *
 * @package Gangotri_Expeditions
 */
(function () {
	'use strict';

	/** Highest index already used in this repeater, so a new row cannot collide. */
	function nextIndex(repeater) {
		let max = -1;

		repeater.querySelectorAll('[data-ge-rows] [name]').forEach(function (input) {
			const match = input.name.match(/\[(\d+)\]/);
			if (match) {
				max = Math.max(max, parseInt(match[1], 10));
			}
		});

		return max + 1;
	}

	function addRow(repeater) {
		const template = repeater.querySelector('[data-ge-template]');
		const rows = repeater.querySelector('[data-ge-rows]');
		if (!template || !rows) return;

		const index = nextIndex(repeater);
		const row = template.content.firstElementChild.cloneNode(true);

		row.querySelectorAll('[name]').forEach(function (input) {
			input.name = input.name.replace('__INDEX__', String(index));
		});

		rows.append(row);
		row.querySelector('input, textarea')?.focus();
	}

	document.addEventListener('click', function (e) {
		const add = e.target.closest('[data-ge-add]');
		if (add) {
			e.preventDefault();
			addRow(add.closest('[data-ge-repeater]'));
			return;
		}

		const remove = e.target.closest('[data-ge-remove]');
		if (remove) {
			e.preventDefault();
			const row = remove.closest('[data-ge-row]');
			const rows = row?.parentElement;

			// Keep one empty row rather than leaving the field with no way back.
			if (rows && rows.querySelectorAll('[data-ge-row]').length === 1) {
				row.querySelectorAll('input, textarea').forEach(function (input) {
					input.value = '';
				});
				return;
			}
			row?.remove();
		}
	});

	/**
	 * Enter inside a single-line repeater row adds the next row instead of
	 * submitting the post - the same key that ends a bullet in any editor.
	 */
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Enter' || e.target.tagName !== 'INPUT') return;

		const repeater = e.target.closest('[data-ge-repeater]');
		if (!repeater || e.target.closest('.ge-row--group')) return;

		e.preventDefault();
		addRow(repeater);
	});
})();
