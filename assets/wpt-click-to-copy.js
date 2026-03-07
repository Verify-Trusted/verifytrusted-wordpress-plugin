/**
 * Headwall WP Tutorials Click-to-Copy : WPTCTC
 *
 * https://wp-tutorials.tech/refine-wordpress/reusable-javascript-click-to-copy/
 *
 * Changelog
 *
 * 2023-12-19 : Added support for a "copy-html" CSS class, so the element's
 *              innerHTML is used to puplate dataset.ctc (instead of innerText).
 *              To copy HTML, add "click-to-copy copy-html" or
 *              "fancy-click-to-copy copy-html" CSS classes to your element.
 *
 *              Added support for legacy copy-text when navigvator.clipboard is
 *              not available. This is useful as a fallback when running the code
 *              over "http" instead of "https".
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // Diagnostics
  // console.log('WPT Click-to-copy : load');

  // Only proceed if wptClickToCopy has been specified as a
  // global JS variable.
  if (typeof wptClickToCopy !== 'undefined') {
    /**
     * Find all elements with either "click-to-copy" or "fancy-click-to-copy"
     * class and configure them.
     */
    wptClickToCopy.init = () => {
      // Diagnostics
      // console.log('WPT Click-to-copy : init');

      document.querySelectorAll('.click-to-copy, .fancy-click-to-copy').forEach(function (container) {
        // Make sure the element has "click-to-copy" as one of its classes,
        // as it might only have fancy-click-to-copy.
        if (!container.classList.contains('click-to-copy')) {
          container.classList.add('click-to-copy');
        }

        // The actual text being copied is in the data-ctc="..." attribute.
        // If this hasn't been set, copy the element's inner text (or inner
        // HTML) into the data attribute now.
        if (typeof container.dataset.ctc !== 'undefined') {
          // the click-to-copy text has already been specified, so we don't
          // need to do anything.
        } else if (container.classList.contains('copy-html')) {
          container.dataset.ctc = container.innerHTML;
        } else {
          container.dataset.ctc = container.innerText;
        }

        // Add the element's click event handler.
        container.addEventListener('click', wptClickToCopy.clickHandler);
      });
    };

    /**
     * Handle click events on our click-to=-copy elements.
     *
     * @param  object event   JavaScript event
     */
    wptClickToCopy.clickHandler = (event) => {
      event.preventDefault();

      // The user might have clicked on a child of the click-to-copy element
      // (or on the tool tip) sowe need to use .closest() to recurse up the
      // DOM and find the correct element. e.g. this could be event.target,
      // event.target.parentElement or even event.target.parentElement.parentElement
      let container = event.target.closest('.click-to-copy');
      let copyText = '';

      if (!container) {
        // The clicked element doesn't have the "click-to-copy" class.
      } else if (wptClickToCopy.hasTip(container)) {
        // The container already has a tip showing.
      } else if ((copyText = container.dataset.ctc).length == 0) {
        // There's no text to copy (empty string)
      } else {
        // If the site is using HTTPS then we can use navigator.clipboard to
        // copy the text. Otherwise we need to create a temporary textarea
        // element, populate it and call document.execCommand('copy')...
        // it's a bit of a fiddle, but it should work in older browsers and
        // with non-https connections.
        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(copyText);
        } else if (typeof document.execCommand === 'function') {
          const textArea = document.createElement('textarea');
          textArea.value = copyText;
          textArea.style.position = 'absolute';
          textArea.style.left = '-100px';
          textArea.style.top = '0px';
          textArea.style.width = '50px';
          document.body.prepend(textArea);

          try {
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
          } catch (error) {
            console.error(error);
          } finally {
            textArea.remove();
          }
        } else {
          console.error('Unable to copy the text');
        }

        // Add a temporary tooltip to the click-to-copy element, using the
        // contents of wptClickToCopy.tipCopied as the label (passed from
        // the back-end PHP).
        wptClickToCopy.addTip(container, wptClickToCopy.tipCopied);
      }
    };

    /**
     * Utility function that tells us if an element already has a tooltip,
     * or not.
     *
     * @param  object   container   The click-to-copy element.
     * @return bool                 Does this element have a child tooltip
     */
    wptClickToCopy.hasTip = (container) => {
      return container.querySelectorAll('.ctc-tip').length > 0;
    };

    /**
     * Remove all child tool tips from this element.
     *
     * @param  object   container   The click-to-copy element.
     */
    wptClickToCopy.removeTip = (container) => {
      container.querySelectorAll('.ctc-tip').forEach((tip) => {
        // console.log(`Remove: ${tip.innerText}`);
        tip.remove();
      });
    };

    /**
     * Add a tool tip to this element.
     *
     * @param  object   container   The click-to-copy element.
     * @param  string   tipText     The text to show in the tool tip
     */
    wptClickToCopy.addTip = (container, tipText) => {
      const tip = document.createElement('div');
      tip.classList.add('ctc-tip');
      tip.append(tipText);
      container.append(tip);

      // After the number of milliseconds specified in "wptClickToCopy.tipTimeout",
      // remove the tool tip form the DOM.
      if (wptClickToCopy.tipTimeout > 0) {
        setTimeout(() => {
          tip.remove();
        }, wptClickToCopy.tipTimeout);
      }
    };

    /**
     * Main entry point.
     */
    wptClickToCopy.init();
  }
});
