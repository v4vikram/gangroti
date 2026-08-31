/**
 * Repeater rows and image pickers for the theme's meta boxes.
 *
 * Vanilla, no jQuery, no build step - it is admin-only and small enough that
 * putting it through the front-end pipeline would cost more than it saves.
 */
(function () {
	'use strict';

	/* ------------------------------------------------------------ repeaters */

	/**
	 * Rows are indexed by their position in the DOM rather than by a counter,
	 * so deleting a middle row cannot leave a gap that PHP would read as a
	 * different row.
	 */
	function reindex( repeater ) {
		const rows = repeater.querySelectorAll( '[data-ge-rows] > [data-ge-row]' );

		rows.forEach( function ( row, i ) {
			row.querySelectorAll( '[name]' ).forEach( function ( input ) {
				input.name = input.name.replace( /\[(?:__i__|\d+)\]/, '[' + i + ']' );
			} );
		} );
	}

	function addRow( repeater ) {
		const template = repeater.querySelector( '[data-ge-template]' );
		const rows = repeater.querySelector( '[data-ge-rows]' );
		if ( ! template || ! rows ) {
			return;
		}

		// PHP escapes the blank row before printing it, so the <template> holds
		// one text node reading "<div ...>" rather than the elements
		// themselves. innerHTML gives that text back still escaped, which
		// parses to nothing - textContent is what unescapes it.
		//
		// Both shapes are handled because the escaping is a detail of the PHP
		// side: if it ever stops escaping, the template carries real elements
		// and this must not start feeding their markup through a second parse.
		const frag = template.content;
		const escaped = ! frag || ! frag.querySelector( '[data-ge-row]' );

		const holder = document.createElement( 'div' );
		holder.innerHTML = escaped
			? ( frag ? frag.textContent : template.textContent ) || ''
			: template.innerHTML;

		const row = holder.querySelector( '[data-ge-row]' );
		if ( ! row ) {
			return;
		}

		rows.append( row );
		reindex( repeater );
		row.querySelector( 'input, textarea, select' )?.focus();
	}

	document.addEventListener( 'click', function ( e ) {
		const add = e.target.closest( '[data-ge-add]' );
		if ( add ) {
			e.preventDefault();
			addRow( add.closest( '[data-ge-repeater]' ) );
			return;
		}

		const remove = e.target.closest( '[data-ge-remove]' );
		if ( remove ) {
			e.preventDefault();
			const repeater = remove.closest( '[data-ge-repeater]' );
			const row = remove.closest( '[data-ge-row]' );
			const rows = repeater.querySelectorAll( '[data-ge-rows] > [data-ge-row]' );

			// Keep one row present, so the control never disappears entirely.
			if ( rows.length > 1 ) {
				row.remove();
			} else {
				row.querySelectorAll( 'input, textarea' ).forEach( function ( i ) {
					i.value = '';
				} );
			}
			reindex( repeater );
		}
	} );

	/* -------------------------------------------------------- drag to sort */

	let dragging = null;

	document.addEventListener( 'mousedown', function ( e ) {
		const handle = e.target.closest( '[data-ge-handle]' );
		if ( ! handle ) {
			return;
		}
		dragging = handle.closest( '[data-ge-row]' );
		dragging.draggable = true;
	} );

	document.addEventListener( 'dragstart', function ( e ) {
		if ( ! dragging ) {
			return;
		}
		e.dataTransfer.effectAllowed = 'move';
		dragging.classList.add( 'is-dragging' );
	} );

	document.addEventListener( 'dragover', function ( e ) {
		if ( ! dragging ) {
			return;
		}
		e.preventDefault();

		const over = e.target.closest( '[data-ge-row]' );
		if ( ! over || over === dragging || over.parentElement !== dragging.parentElement ) {
			return;
		}

		// Insert before or after depending on which half of the row we are over.
		const box = over.getBoundingClientRect();
		const after = e.clientY > box.top + box.height / 2;
		over.parentElement.insertBefore( dragging, after ? over.nextSibling : over );
	} );

	document.addEventListener( 'dragend', function () {
		if ( ! dragging ) {
			return;
		}
		dragging.classList.remove( 'is-dragging' );
		dragging.draggable = false;
		reindex( dragging.closest( '[data-ge-repeater]' ) );
		dragging = null;
	} );

	/* ----------------------------------------------------------- image pick */

	document.addEventListener( 'click', function ( e ) {
		const pick = e.target.closest( '[data-ge-image-pick]' );
		const clear = e.target.closest( '[data-ge-image-clear]' );

		if ( clear ) {
			e.preventDefault();
			const box = clear.closest( '[data-ge-image]' );
			box.querySelector( '[data-ge-image-input]' ).value = '';
			box.querySelector( '.ge-image__preview' ).innerHTML = '';
			return;
		}

		if ( ! pick || ! window.wp?.media ) {
			return;
		}

		e.preventDefault();
		const box = pick.closest( '[data-ge-image]' );

		const frame = window.wp.media( {
			title: 'Choose image',
			button: { text: 'Use this image' },
			multiple: false,
			library: { type: 'image' },
		} );

		frame.on( 'select', function () {
			const item = frame.state().get( 'selection' ).first().toJSON();
			const url = item.sizes?.medium?.url ?? item.url;

			box.querySelector( '[data-ge-image-input]' ).value = item.id;
			box.querySelector( '.ge-image__preview' ).innerHTML =
				'<img src="' + url + '" alt="">';
		} );

		frame.open();
	} );
})();
