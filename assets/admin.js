/**
 * Click-to-copy behaviour for the shortcode snippets on the Verify Trusted
 * settings page.
 *
 * The plugin emits no inline script, so every string this file needs arrives
 * on the button as a data- attribute rather than via wp_localize_script().
 *
 * Two copy strategies are attempted, in order:
 *
 *   1. navigator.clipboard.writeText() - the modern path, but it is only
 *      defined in a secure context. Plenty of local and staging wp-admins are
 *      served over plain HTTP, where it is simply absent.
 *   2. A hidden textarea plus document.execCommand( 'copy' ) - deprecated, but
 *      it is what keeps the button working on those non-HTTPS origins.
 *
 * If neither is available the buttons hide themselves, leaving the shortcode
 * text on screen to select by hand.
 *
 * @since 1.5.0
 */
( function () {
	'use strict';

	/**
	 * How long the "Copied!" label stays on the button, in milliseconds.
	 *
	 * @type {number}
	 */
	var FEEDBACK_TIMEOUT_MS = 2000;

	/**
	 * Pending label-reset timers, keyed by button.
	 *
	 * @type {WeakMap}
	 */
	var resetTimers = new WeakMap();

	/**
	 * Is the async Clipboard API usable in this context?
	 *
	 * @return {boolean} True when navigator.clipboard.writeText() exists.
	 */
	function hasClipboardApi() {
		return !! (
			window.navigator &&
			window.navigator.clipboard &&
			'function' === typeof window.navigator.clipboard.writeText
		);
	}

	/**
	 * Is the execCommand fallback usable in this context?
	 *
	 * @return {boolean} True when document.execCommand() exists.
	 */
	function hasExecCommand() {
		return 'function' === typeof document.execCommand;
	}

	/**
	 * Copy text via a hidden textarea and document.execCommand().
	 *
	 * The textarea is positioned off-screen rather than hidden with
	 * display:none, because a hidden element cannot hold a selection.
	 *
	 * @param {string} text The text to place on the clipboard.
	 *
	 * @return {boolean} True if the copy succeeded.
	 */
	function copyWithExecCommand( text ) {
		var textarea = document.createElement( 'textarea' );
		var copied = false;

		textarea.value = text;
		textarea.setAttribute( 'readonly', '' );
		textarea.setAttribute( 'aria-hidden', 'true' );
		textarea.style.position = 'fixed';
		textarea.style.top = '0';
		textarea.style.left = '-9999px';

		document.body.appendChild( textarea );
		textarea.select();
		textarea.setSelectionRange( 0, textarea.value.length );

		try {
			copied = document.execCommand( 'copy' );
		} catch ( error ) {
			copied = false;
		}

		document.body.removeChild( textarea );

		return copied;
	}

	/**
	 * Announce a message to assistive technology.
	 *
	 * @param {string} message The message to announce.
	 */
	function announce( message ) {
		var region = document.querySelector( '[data-vtrust-copy-status]' );

		if ( region ) {
			region.textContent = message;
		}
	}

	/**
	 * Swap the button label to a result message, then restore it.
	 *
	 * @param {HTMLElement} button  The button that was activated.
	 * @param {boolean}     success Whether the copy succeeded.
	 */
	function showFeedback( button, success ) {
		var idleLabel = button.getAttribute( 'data-vtrust-label-copy' );
		var message = success
			? button.getAttribute( 'data-vtrust-label-copied' )
			: button.getAttribute( 'data-vtrust-label-failed' );

		button.textContent = message;
		button.classList.remove( 'is-copied', 'is-error' );
		button.classList.add( success ? 'is-copied' : 'is-error' );

		announce( message );

		window.clearTimeout( resetTimers.get( button ) );

		resetTimers.set(
			button,
			window.setTimeout( function () {
				button.textContent = idleLabel;
				button.classList.remove( 'is-copied', 'is-error' );
				announce( '' );
			}, FEEDBACK_TIMEOUT_MS )
		);
	}

	/**
	 * Handle a click on a copy button.
	 *
	 * @param {Event} event The click event.
	 */
	function handleClick( event ) {
		var button = event.currentTarget;
		var text = button.getAttribute( 'data-vtrust-copy' );

		if ( ! text ) {
			return;
		}

		if ( hasClipboardApi() ) {
			window.navigator.clipboard.writeText( text ).then(
				function () {
					showFeedback( button, true );
				},
				function () {
					// The API exists but refused - permissions policy, or the
					// document lost focus. Fall through to the legacy path.
					showFeedback( button, hasExecCommand() && copyWithExecCommand( text ) );
				}
			);
		} else {
			showFeedback( button, copyWithExecCommand( text ) );
		}
	}

	/**
	 * Wire up every copy button on the page.
	 */
	function init() {
		var buttons = document.querySelectorAll( '[data-vtrust-copy]' );
		var canCopy = hasClipboardApi() || hasExecCommand();
		var index;

		for ( index = 0; index < buttons.length; index++ ) {
			if ( canCopy ) {
				buttons[ index ].addEventListener( 'click', handleClick );
			} else {
				// Nothing to offer - hide the control rather than leave a
				// button that silently does nothing. The snippet text stays
				// on screen for manual selection.
				buttons[ index ].hidden = true;
			}
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
