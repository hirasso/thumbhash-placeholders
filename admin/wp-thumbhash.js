"use strict";

/**
 * @typedef {import('jquery')} jQuery
 * @typedef {import('jqueryui')} jQueryUI
 */

/**
 * @typedef {object} CustomWindowObject
 * @property {string} [ajaxurl]
 */

/**
 * @typedef {Window & CustomWindowObject} CustomWindow
 */

(($) => {
  /**
   * Create an element on the fly
   * @param {string} html - The HTML string to create the element from.
   * @return {HTMLElement} The created element.
   */
  function createElement(html) {
    const template = document.createElement("template");
    template.innerHTML = html;
    return /** @type {HTMLElement} */ (template.content.children[0]);
  }

  class WPThumbhashField extends HTMLElement {
    /** @type {number} attachmentID */
    attachmendID;

    constructor() {
      super();
      const button = /** @type {!HTMLButtonElement} */ (
        this.querySelector("[data-wp-thumbhash-generate]")
      );
      this.attachmendID = parseInt(String(this.getAttribute("data-id")), 10);
      button.addEventListener("click", this.generate);
    }

    generate = () => {
      $.ajax({
        /** @ts-ignore */
        url: window.ajaxurl,
        method: "POST",
        data: {
          action: window.wpThumbhash.action,
          /** @ts-ignore */
          security: window.wpThumbhash.nonce,
          id: this.attachmendID, // Your custom data
        },
        success: (response) => {
          const { html } = response?.data;
          const { success } = response;
          if (success) {
            this.replaceWith(createElement(html));
          }
        },
        error: (error) => {
          console.error("AJAX Error:", error);
        },
      });
    };
  }

  customElements.define("wp-thumbhash-field", WPThumbhashField);
})(/** @type {jQuery} */ jQuery);
