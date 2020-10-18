/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-login-submit');

    if (btn) {
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        if (document.formvalidator.isValid(btn.form)) {
          Joomla.submitbutton('login');
        }
      });
    }
  });
})(window.Joomla, document);
