/**
 * Provides a {@link jQuery} plugin that add suggestions to a text box.
 *
 * @module jquery.suggestions
 */

( function () {

	/**
	 * Cancel any delayed maybeFetch() call and callback the context so
	 * they can cancel any async fetching if they use AJAX or something.
	 *
	 * @param {Object} context
	 */
	function cancel( context ) {
		if ( context.data.timerID !== null ) {
			clearTimeout( context.data.timerID );
		}
		if ( typeof context.config.cancel === 'function' ) {
			context.config.cancel.call( context.data.$textbox );
		}
	}

	/**
	 * Hide the element with suggestions and clean up some state.
	 *
	 * @param {Object} context
	 */
	function hide( context ) {
		// Remove any highlights, including on "special" items
		context.data.$container.find( '.suggestions-result-current' ).removeClass( 'suggestions-result-current' );
		// Hide the container
		context.data.$container.hide();
	}

	/**
	 * Restore the text the user originally typed in the textbox, before it
	 * was overwritten by highlight(). This restores the value the currently
	 * displayed suggestions are based on, rather than the value just before
	 * highlight() overwrote it; the former is arguably slightly more sensible.
	 *
	 * @param {Object} context
	 */
	function restore( context ) {
		context.data.$textbox.val( context.data.prevText );
	}

	/**
	 * @param {Object} context
	 */
	function special( context ) {
		// Allow custom rendering - but otherwise don't do any rendering
		if ( typeof context.config.special.render === 'function' ) {
			// Wait for the browser to update the value
			setTimeout( () => {
				// Render special
				const $special = context.data.$container.find( '.suggestions-special' );
				context.config.special.render.call( $special, context.data.$textbox.val(), context );
			}, 1 );
		}
	}

	/**
	 * Ask the user-specified callback for new suggestions. Any previous delayed
	 * call to this function still pending will be canceled. If the value in the
	 * textbox is empty or hasn't changed since the last time suggestions were fetched,
	 * this function does nothing.
	 *
	 * @param {Object} context
	 * @param {boolean} delayed Whether or not to delay this by the currently configured amount of time
	 */
	function update( context, delayed ) {
		function maybeFetch() {
			if ( typeof context.config.update.before === 'function' ) {
				context.config.update.before.call( context.data.$textbox );
			}

			const val = context.data.$textbox.val(),
				cache = context.data.cache;

			let cacheHit = false;
			// Only fetch if the value in the textbox changed and is not empty, or if the results were hidden
			// if the textbox is empty then clear the result div, but leave other settings intouched
			if ( val.length === 0 ) {
				hide( context );
				context.data.prevText = '';
			} else if (
				val !== context.data.prevText ||
				// eslint-disable-next-line no-jquery/no-sizzle
				!context.data.$container.is( ':visible' )
			) {
				context.data.prevText = val;
				// Try cache first
				if ( context.config.cache && val in cache ) {
					if ( mw.now() - cache[ val ].timestamp < context.config.cacheMaxAge ) {
						context.data.$textbox.suggestions( 'suggestions', cache[ val ].suggestions );
						if ( typeof context.config.update.after === 'function' ) {
							context.config.update.after.call( context.data.$textbox, cache[ val ].metadata );
						}
						cacheHit = true;
					} else {
						// Cache expired
						delete cache[ val ];
					}
				}
				if ( !cacheHit && typeof context.config.fetch === 'function' ) {
					context.config.fetch.call(
						context.data.$textbox,
						val,
						( suggestions, metadata ) => {
							suggestions = suggestions.slice( 0, context.config.maxRows );
							context.data.$textbox.suggestions( 'suggestions', suggestions );
							if ( typeof context.config.update.after === 'function' ) {
								context.config.update.after.call( context.data.$textbox, metadata );
							}
							if ( context.config.cache ) {
								cache[ val ] = {
									suggestions: suggestions,
									metadata: metadata,
									timestamp: mw.now()
								};
							}
						},
						context.config.maxRows
					);
				}
			}

			// Always update special rendering
			special( context );
		}

		// Cancels any delayed maybeFetch call, and invokes context.config.cancel.
		cancel( context );

		if ( delayed ) {
			// To avoid many started/aborted requests while typing, we're gonna take a short
			// break before trying to fetch data.
			context.data.timerID = setTimeout( maybeFetch, context.config.delay );
		} else {
			maybeFetch();
		}
	}

	/**
	 * Highlight a result in the results table
	 *
	 * @param {Object} context
	 * @param {jQuery|string} $result `<tr>` to highlight, or 'prev' or 'next'
	 * @param {boolean} updateTextbox If true, put the suggestion in the textbox
	 */
	function highlight( context, $result, updateTextbox ) {
		const $selected = context.data.$container.find( '.suggestions-result-current' );
		if ( !$result.get || $selected.get( 0 ) !== $result.get( 0 ) ) {
			if ( $result === 'prev' ) {
				// eslint-disable-next-line no-jquery/no-class-state
				if ( $selected.hasClass( 'suggestions-special' ) ) {
					$result = context.data.$container.find( '.suggestions-result' ).last();
				} else {
					$result = $selected.prev();
					// eslint-disable-next-line no-jquery/no-class-state
					if ( !( $result.length && $result.hasClass( 'suggestions-result' ) ) ) {
						// there is something in the DOM between selected element and the wrapper, bypass it
						$result = $selected.parents( '.suggestions-results > *' ).prev().find( '.suggestions-result' ).eq( 0 );
					}

					if ( $selected.length === 0 ) {
						// we are at the beginning, so lets jump to the last item
						if ( context.data.$container.find( '.suggestions-special' ).html() !== '' ) {
							$result = context.data.$container.find( '.suggestions-special' );
						} else {
							$result = context.data.$container.find( '.suggestions-results .suggestions-result' ).last();
						}
					}
				}
			} else if ( $result === 'next' ) {
				if ( $selected.length === 0 ) {
					// No item selected, go to the first one
					$result = context.data.$container.find( '.suggestions-results .suggestions-result' ).first();
					if ( $result.length === 0 && context.data.$container.find( '.suggestions-special' ).html() !== '' ) {
						// No suggestion exists, go to the special one directly
						$result = context.data.$container.find( '.suggestions-special' );
					}
				} else {
					$result = $selected.next();
					// eslint-disable-next-line no-jquery/no-class-state
					if ( !( $result.length && $result.hasClass( 'suggestions-result' ) ) ) {
						// there is something in the DOM between selected element and the wrapper, bypass it
						$result = $selected.parents( '.suggestions-results > *' ).next().find( '.suggestions-result' ).eq( 0 );
					}

					// eslint-disable-next-line no-jquery/no-class-state
					if ( $selected.hasClass( 'suggestions-special' ) ) {
						$result = $( [] );
					} else if (
						$result.length === 0 &&
						context.data.$container.find( '.suggestions-special' ).html() !== ''
					) {
						// We were at the last item, jump to the specials!
						$result = context.data.$container.find( '.suggestions-special' );
					}
				}
			}
			$selected.removeClass( 'suggestions-result-current' );
			$result.addClass( 'suggestions-result-current' );
		}
		if ( updateTextbox ) {
			if ( $result.length === 0 || $result.is( '.suggestions-special' ) ) {
				restore( context );
			} else {
				context.data.$textbox.val( $result.data( 'text' ) );
				// .val() doesn't call any event handlers, so
				// let the world know what happened
				context.data.$textbox.trigger( 'change' );
			}
			context.data.$textbox.trigger( 'change' );
		}
	}

	/**
	 * Sets the value of a property, and updates the widget accordingly
	 *
	 * @param {Object} context
	 * @param {string} property Name of property
	 * @param {any} value Value to set property with
	 */
	function configure( context, property, value ) {

		// Validate creation using fallback values
		switch ( property ) {
			case 'fetch':
			case 'cancel':
			case 'special':
			case 'result':
			case 'update':
			case '$region':
			case 'expandFrom':
				context.config[ property ] = value;
				break;
			case 'suggestions':
				context.config[ property ] = value;
				// Update suggestions
				if ( context.data !== undefined ) {
					if ( context.data.$textbox.val().length === 0 ) {
						// Hide the div when no suggestion exist
						hide( context );
					} else {
						// Rebuild the suggestions list
						context.data.$container.show();
						// Update the size and position of the list
						const regionIsFixed = ( function () {
							let $el = context.config.$region;
							do {
								if ( $el.css( 'position' ) === 'fixed' ) {
									return true;
								}
								$el = $( $el[ 0 ].offsetParent );
							} while ( $el.length );
							return false;
						}() );
						const regionPosition = regionIsFixed ?
							context.config.$region[ 0 ].getBoundingClientRect() :
							context.config.$region.offset();
						const newCSS = {
							position: regionIsFixed ? 'fixed' : 'absolute',
							top: regionPosition.top + context.config.$region.outerHeight(),
							bottom: 'auto',
							width: context.config.$region.outerWidth(),
							height: 'auto'
						};

						// Process expandFrom, after this it is set to left or right.
						context.config.expandFrom = ( function ( expandFrom ) {
							const isRTL = $( document.documentElement ).css( 'direction' ) === 'rtl',
								$region = context.config.$region;

							// Backwards compatible
							if ( context.config.positionFromLeft ) {
								expandFrom = 'left';

							// Catch invalid values, default to 'auto'
							} else if ( ![ 'left', 'right', 'start', 'end', 'auto' ].includes( expandFrom ) ) {
								expandFrom = 'auto';
							}

							if ( expandFrom === 'auto' ) {
								if ( $region.data( 'searchsuggest-expand-dir' ) ) {
									// If the markup explicitly contains a direction, use it.
									expandFrom = $region.data( 'searchsuggest-expand-dir' );
								} else {
									const regionWidth = $region.outerWidth();
									const docWidth = $( document ).width();
									if ( regionWidth > ( 0.85 * docWidth ) ) {
										// If the input size takes up more than 85% of the document horizontally
										// expand the suggestions to the writing direction's native end.
										expandFrom = 'start';
									} else {
										// Calculate the center points of the input and document
										const regionCenter = regionPosition.left + regionWidth / 2;
										const docCenter = docWidth / 2;
										if ( Math.abs( regionCenter - docCenter ) < ( 0.10 * docCenter ) ) {
											// If the input's center is within 10% of the document center
											// use the writing direction's native end.
											expandFrom = 'start';
										} else {
											// Otherwise expand the input from the closest side of the page,
											// towards the side of the page with the most free open space
											expandFrom = regionCenter > docCenter ? 'right' : 'left';
										}
									}
								}
							}

							if ( expandFrom === 'start' ) {
								expandFrom = isRTL ? 'right' : 'left';

							} else if ( expandFrom === 'end' ) {
								expandFrom = isRTL ? 'left' : 'right';
							}

							return expandFrom;

						}( context.config.expandFrom ) );

						if ( context.config.expandFrom === 'left' ) {
							// Expand from left
							newCSS.left = regionPosition.left;
							newCSS.right = 'auto';
						} else {
							// Expand from right
							newCSS.left = 'auto';
							newCSS.right = document.documentElement.clientWidth -
								( regionPosition.left + context.config.$region.outerWidth() );
						}

						context.data.$container.css( newCSS );
						const $results = context.data.$container.children( '.suggestions-results' );
						$results.empty();
						let expWidth = -1;
						for ( let i = 0; i < context.config.suggestions.length; i++ ) {
							const text = context.config.suggestions[ i ];
							const $result = $( '<div>' )
								.addClass( 'suggestions-result' )
								.attr( 'rel', i )
								.data( 'text', context.config.suggestions[ i ] )
								.on( 'mousemove', function () {
									context.data.selectedWithMouse = true;
									highlight(
										context,
										$( this ).closest( '.suggestions-results .suggestions-result' ),
										false
									);
								} )
								.appendTo( $results );
							// Allow custom rendering
							if ( typeof context.config.result.render === 'function' ) {
								context.config.result.render.call( $result, context.config.suggestions[ i ], context );
							} else {
								$result.text( text );
							}

							if ( context.config.highlightInput ) {
								$result.highlightText( context.data.prevText, { method: 'prefixPlusComboHighlight' } );
							}

							// Widen results box if needed (new width is only calculated here, applied later).

							// The monstrosity below accomplishes two things:
							// * Wraps the text contents in a DOM element, so that we can know its width. There is
							//   no way to directly access the width of a text node, and we can't use the parent
							//   node width as it has text-overflow: ellipsis; and overflow: hidden; applied to
							//   it, which trims it to a smaller width.
							// * Temporarily applies position: absolute; to the wrapper to pull it out of normal
							//   document flow. Otherwise the CSS text-overflow: ellipsis; and overflow: hidden;
							//   rules would cause some browsers (at least all versions of IE from 6 to 11) to
							//   still report the "trimmed" width. This should not be done in regular CSS
							//   stylesheets as we don't want this rule to apply to other <span> elements, like
							//   the ones generated by jquery.highlightText.
							const $spanForWidth = $result.wrapInner( '<span>' ).children();
							const childrenWidth = $spanForWidth.css( 'position', 'absolute' ).outerWidth();
							$spanForWidth.contents().unwrap();

							if ( childrenWidth > $result.width() && childrenWidth > expWidth ) {
								// factor in any padding, margin, or border space on the parent
								expWidth = childrenWidth + ( context.data.$container.width() - $result.width() );
							}
						}

						// Apply new width for results box, if any
						if ( expWidth > context.data.$container.width() ) {
							const maxWidth = context.config.maxExpandFactor * context.data.$textbox.width();
							context.data.$container.width( Math.min( expWidth, maxWidth ) );
						}
					}
				}
				break;
			case 'maxRows':
				context.config[ property ] = Math.max( 1, Math.min( 100, value ) );
				break;
			case 'delay':
				context.config[ property ] = Math.max( 0, Math.min( 1200, value ) );
				break;
			case 'cacheMaxAge':
				context.config[ property ] = Math.max( 1, value );
				break;
			case 'maxExpandFactor':
				context.config[ property ] = Math.max( 1, value );
				break;
			case 'cache':
			case 'submitOnClick':
			case 'positionFromLeft':
			case 'highlightInput':
				context.config[ property ] = !!value;
				break;
		}
	}

	/**
	 * Respond to keypress event
	 *
	 * @param {jQuery.Event} e
	 * @param {Object} context
	 * @param {number} key Code of key pressed
	 */
	function keypress( e, context, key ) {
		// eslint-disable-next-line no-jquery/no-sizzle
		const wasVisible = context.data.$container.is( ':visible' );
		let preventDefault = false;

		switch ( key ) {
			// Arrow down
			case 40:
				if ( wasVisible ) {
					highlight( context, 'next', true );
					context.data.selectedWithMouse = false;
				} else {
					update( context, false );
				}
				preventDefault = true;
				break;
			// Arrow up
			case 38:
				if ( wasVisible ) {
					highlight( context, 'prev', true );
					context.data.selectedWithMouse = false;
				}
				preventDefault = wasVisible;
				break;
			// Escape
			case 27:
				hide( context );
				restore( context );
				cancel( context );
				context.data.$textbox.trigger( 'change' );
				preventDefault = wasVisible;
				break;
			// Enter
			case 13: {
				preventDefault = wasVisible;
				const $selected = context.data.$container.find( '.suggestions-result-current' );
				hide( context );
				if ( $selected.length === 0 || context.data.selectedWithMouse ) {
					// If nothing is selected or if something was selected with the mouse
					// cancel any current requests and allow the form to be submitted
					// (simply don't prevent default behavior).
					cancel( context );
					preventDefault = false;
				} else if ( $selected.is( '.suggestions-special' ) ) {
					if ( typeof context.config.special.select === 'function' ) {
						// Allow the callback to decide whether to prevent default or not
						if ( context.config.special.select.call( $selected, context.data.$textbox, 'keyboard' ) === true ) {
							preventDefault = false;
						}
					}
				} else {
					if ( typeof context.config.result.select === 'function' ) {
						// Allow the callback to decide whether to prevent default or not
						if ( context.config.result.select.call( $selected, context.data.$textbox, 'keyboard' ) === true ) {
							preventDefault = false;
						}
					}
				}
				break;
			}
			default:
				update( context, true );
				break;
		}
		if ( preventDefault ) {
			e.preventDefault();
			e.stopPropagation();
		}
	}

	/**
	 * Add suggestions to a text box.
	 *
	 * Set options:
	 *
	 * ```
	 * $( '#textbox' ).suggestions( { option1: value1, option2: value2 } );
	 * $( '#textbox' ).suggestions( option, value );
	 * ```
	 *
	 * To use this {@link jQuery} plugin, load the `jquery.suggestions` module with {@link mw.loader}.
	 *
	 * @example
	 * // Initialize:
	 * mw.loader.using( 'jquery.suggestions' ).then(()=> {
	 *  $( '#textbox' ).suggestions();
	 * });
	 * @memberof module:jquery.suggestions
	 * @return {jQuery}
	 *
	 * @param {Object} options
	 *
	 * @param {Function} [options.fetch] Callback that should fetch suggestions and set the suggestions
	 *  property. Called in context of the text box.
	 * @param {string} options.fetch.query
	 * @param {Function} options.fetch.response Callback to receive the suggestions with
	 * @param {Array} options.fetch.response.suggestions
	 * @param {number} options.fetch.maxRows
	 *
	 * @param {Function} [options.cancel] Callback function to call when any pending asynchronous
	 *  suggestions fetches. Called in context of the text box.
	 *
	 * @param {Object} [options.special] Set of callbacks for rendering and selecting.
	 *
	 * @param {Function} options.special.render Called in context of the suggestions-special element.
	 * @param {string} options.special.render.query
	 * @param {Object} options.special.render.context
	 *
	 * @param {Function} options.special.select Called in context of the suggestions-result-current element.
	 * @param {jQuery} options.special.select.$textbox
	 *
	 * @param {Object} [options.result] Set of callbacks for rendering and selecting
	 *
	 * @param {Function} options.result.render Called in context of the suggestions-result element.
	 * @param {string} options.result.render.suggestion
	 * @param {Object} options.result.render.context
	 *
	 * @param {Function} options.result.select Called in context of the suggestions-result-current element.
	 * @param {jQuery} options.result.select.$textbox
	 *
	 * @param {Object} [options.update] Set of callbacks for listening to a change in the text input.
	 *
	 * @param {Function} options.update.before Called right after the user changes the textbox text.
	 * @param {Function} options.update.after Called after results are updated either from the cache or
	 * the API as a result of the user input.
	 *
	 * @param {jQuery} [options.$region=this] The element to place the suggestions below and match width of.
	 *
	 * @param {string[]} [options.suggestions] Array of suggestions to display.
	 *
	 * @param {number} [options.maxRows=10] Maximum number of suggestions to display at one time.
	 *  Must be between 1 and 100.
	 *
	 * @param {number} [options.delay=120] Number of milliseconds to wait for the user to stop typing.
	 *  Must be between 0 and 1200.
	 *
	 * @param {boolean} [options.cache=false] Whether to cache results from a fetch.
	 *
	 * @param {number} [options.cacheMaxAge=60000] Number of milliseconds to cache results from a fetch.
	 *  Must be higher than 1. Defaults to 1 minute.
	 *
	 * @param {boolean} [options.submitOnClick=false] Whether to submit the form containing the textbox
	 *  when a suggestion is clicked.
	 *
	 * @param {number} [options.maxExpandFactor=3] Maximum suggestions box width relative to the textbox
	 *  width. If set to e.g. 2, the suggestions box will never be grown beyond 2 times the width of
	 *  the textbox. Must be higher than 1.
	 *
	 * @param {string} [options.expandFrom=auto] Which direction to offset the suggestion box from.
	 *  Values 'start' and 'end' translate to left and right respectively depending on the directionality
	 *   of the current document, according to `$( document.documentElement ).css( 'direction' )`.
	 *   Valid values: "left", "right", "start", "end", and "auto".
	 *
	 * @param {boolean} [options.positionFromLeft] Sets `expandFrom=left`, for backwards
	 *  compatibility.
	 *
	 * @param {boolean} [options.highlightInput=false] Whether to highlight matched portions of the
	 *  input or not.
	 */
	$.fn.suggestions = function () {
		// Multi-context fields
		const args = arguments;

		$( this ).each( function () {
			/* Construction and Loading */

			let context = $( this ).data( 'suggestions-context' );
			if ( context === undefined || context === null ) {
				context = {
					config: {
						fetch: function () {},
						cancel: function () {},
						special: {},
						result: {},
						update: {},
						$region: $( this ),
						suggestions: [],
						maxRows: 10,
						delay: 120,
						cache: false,
						cacheMaxAge: 60000,
						submitOnClick: false,
						maxExpandFactor: 3,
						expandFrom: 'auto',
						highlightInput: false
					}
				};
			}

			/* API */

			// Handle various calling styles
			if ( args.length > 0 ) {
				if ( typeof args[ 0 ] === 'object' ) {
					// Apply set of properties
					for ( const key in args[ 0 ] ) {
						configure( context, key, args[ 0 ][ key ] );
					}
				} else if ( typeof args[ 0 ] === 'string' ) {
					if ( args.length > 1 ) {
						// Set property values
						configure( context, args[ 0 ], args[ 1 ] );
					}
				}
			}

			/* Initialization */

			if ( context.data === undefined ) {
				context.data = {
					// ID of running timer
					timerID: null,

					// Text in textbox when suggestions were last fetched
					prevText: null,

					// Cache of fetched suggestions
					cache: Object.create( null ),

					// Number of results visible without scrolling
					visibleResults: 0,

					// Suggestion the last mousedown event occurred on
					$mouseDownOn: $( [] ),
					$textbox: $( this ),
					selectedWithMouse: false
				};

				context.data.$container = $( '<div>' )
					.css( 'display', 'none' )
					.addClass( 'suggestions' )
					.append(
						$( '<div>' ).addClass( 'suggestions-results' )
							// Can't use click() because the container div is hidden when the
							// textbox loses focus. Instead, listen for a mousedown followed
							// by a mouseup on the same div.
							.on( 'mousedown', ( e ) => {
								context.data.$mouseDownOn = $( e.target ).closest( '.suggestions-results .suggestions-result' );
							} )
							.on( 'mouseup', ( e ) => {
								const $result = $( e.target ).closest( '.suggestions-results .suggestions-result' ),
									$other = context.data.$mouseDownOn;

								context.data.$mouseDownOn = $( [] );
								if ( $result.get( 0 ) !== $other.get( 0 ) ) {
									return;
								}
								highlight( context, $result, true );
								if ( typeof context.config.result.select === 'function' ) {
									context.config.result.select.call( $result, context.data.$textbox, 'mouse' );
								}
								// Don't interfere with special clicks (e.g. to open in new tab)
								if ( !( e.which !== 1 || e.altKey || e.ctrlKey || e.shiftKey || e.metaKey ) ) {
									// This will hide the link we're just clicking on, which causes problems
									// when done synchronously in at least Firefox 3.6 (T64858).
									setTimeout( () => {
										hide( context );
									} );
								}
								// Always bring focus to the textbox, as that's probably where the user expects it
								// if they were just typing.
								context.data.$textbox.trigger( 'focus' );
							} )
					)
					.append(
						$( '<div>' ).addClass( 'suggestions-special' )
							// Can't use click() because the container div is hidden when the
							// textbox loses focus. Instead, listen for a mousedown followed
							// by a mouseup on the same div.
							.on( 'mousedown', ( e ) => {
								context.data.$mouseDownOn = $( e.target ).closest( '.suggestions-special' );
							} )
							.on( 'mouseup', ( e ) => {
								const $special = $( e.target ).closest( '.suggestions-special' ),
									$other = context.data.$mouseDownOn;

								context.data.$mouseDownOn = $( [] );
								if ( $special.get( 0 ) !== $other.get( 0 ) ) {
									return;
								}
								if ( typeof context.config.special.select === 'function' ) {
									context.config.special.select.call( $special, context.data.$textbox, 'mouse' );
								}
								// Don't interfere with special clicks (e.g. to open in new tab)
								if ( !( e.which !== 1 || e.altKey || e.ctrlKey || e.shiftKey || e.metaKey ) ) {
									// This will hide the link we're just clicking on, which causes problems
									// when done synchronously in at least Firefox 3.6 (T64858).
									setTimeout( () => {
										hide( context );
									} );
								}
								// Always bring focus to the textbox, as that's probably where the user expects it
								// if they were just typing.
								context.data.$textbox.trigger( 'focus' );
							} )
							.on( 'mousemove', ( e ) => {
								context.data.selectedWithMouse = true;
								highlight(
									context, $( e.target ).closest( '.suggestions-special' ), false
								);
							} )
					)
					.appendTo( document.body );

				$( this )
					// Stop browser autocomplete from interfering
					.attr( 'autocomplete', 'off' )
					.on( 'keydown', ( e ) => {
						// Store key pressed to handle later
						context.data.keypressed = e.which;
						context.data.keypressedCount = 0;
					} )
					.on( 'keypress', ( e ) => {
						context.data.keypressedCount++;
						keypress( e, context, context.data.keypressed );
					} )
					.on( 'keyup', ( e ) => {
						// The keypress event is fired when a key is pressed down and that key normally
						// produces a character value. We also want to handle some keys that don't
						// produce a character value so we also attach to the keydown/keyup events.
						// List of codes sourced from
						// https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/keyCode
						const allowed = [
							40, // up arrow
							38, // down arrow
							27, // escape
							13, // enter
							46, // delete
							8 //   backspace
						];
						if ( context.data.keypressedCount === 0 &&
							e.which === context.data.keypressed &&
							allowed.includes( e.which )
						) {
							keypress( e, context, context.data.keypressed );
						}
					} )
					.on( 'blur', () => {
						// When losing focus because of a mousedown
						// on a suggestion, don't hide the suggestions
						if ( context.data.$mouseDownOn.length > 0 ) {
							return;
						}
						hide( context );
						cancel( context );
					} );
				// Load suggestions if the value is changed because there are already
				// typed characters before the JavaScript is loaded.
				if ( $( this ).is( ':focus' ) && this.value !== this.defaultValue ) {
					update( context, false );
				}
			}

			// Store the context for next time
			$( this ).data( 'suggestions-context', context );
		} );
		return this;
	};

	/**
	 * @class jQuery
	 * @mixes jQuery.plugin.suggestions
	 */

}() );
