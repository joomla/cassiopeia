/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
((Joomla) => {
  'use strict';

  class JMultiSelect {
    constructor(formElement) {
      this.tableEl = document.querySelector(formElement);

      if (this.tableEl) {
        this.boxes = [].slice.call(this.tableEl.querySelectorAll('input[type=checkbox]'));
        this.rows = [].slice.call(document.querySelectorAll('tr[class^="row"]'));
        this.checkallToggle = document.querySelector('[name="checkall-toggle"]');

        this.onCheckallToggleClick = this.onCheckallToggleClick.bind(this);
        this.onRowClick = this.onRowClick.bind(this);

        if (this.checkallToggle) {
          this.checkallToggle.addEventListener('click', this.onCheckallToggleClick);
        }

        if (this.rows.length) {
          this.rows.forEach((row) => {
            row.addEventListener('click', this.onRowClick);
          });
        }
      }
    }

    // Changes the background-color on every cell inside a <tr>
    // eslint-disable-next-line class-methods-use-this
    changeBg(row, isChecked) {
      // Check if it should add or remove the background colour
      if (isChecked) {
        [].slice.call(row.querySelectorAll('td, th')).forEach((elementToMark) => {
          elementToMark.classList.add('row-selected');
        });
      } else {
        [].slice.call(row.querySelectorAll('td, th')).forEach((elementToMark) => {
          elementToMark.classList.remove('row-selected');
        });
      }
    }

    onCheckallToggleClick({ target }) {
      const isChecked = target.checked;

      this.rows.forEach((row) => {
        this.changeBg(row, isChecked);
      });
    }

    onRowClick({ target, shiftKey }) {
      // Do not interfere with links or buttons
      if (target.tagName && (target.tagName.toLowerCase() === 'a' || target.tagName.toLowerCase() === 'button')) {
        return;
      }

      if (!this.boxes.length) {
        return;
      }

      const currentRowNum = this.rows.indexOf(target.closest('tr'));
      const currentCheckBox = this.checkallToggle ? currentRowNum + 1 : currentRowNum;
      let isChecked = this.boxes[currentCheckBox].checked;

      if (currentCheckBox >= 0) {
        if (!(target.id === this.boxes[currentCheckBox].id)) {
          // We will prevent selecting text to prevent artifacts
          if (shiftKey) {
            document.body.style['-webkit-user-select'] = 'none';
            document.body.style['-moz-user-select'] = 'none';
            document.body.style['-ms-user-select'] = 'none';
            document.body.style['user-select'] = 'none';
          }

          this.boxes[currentCheckBox].checked = !this.boxes[currentCheckBox].checked;
          isChecked = this.boxes[currentCheckBox].checked;
          Joomla.isChecked(this.boxes[currentCheckBox].checked, this.tableEl.id);
        }

        this.changeBg(this.rows[currentCheckBox - 1], isChecked);

        // Restore normality
        if (shiftKey) {
          document.body.style['-webkit-user-select'] = 'none';
          document.body.style['-moz-user-select'] = 'none';
          document.body.style['-ms-user-select'] = 'none';
          document.body.style['user-select'] = 'none';
        }
      }
    }
  }

  const onBoot = () => {
    if (!Joomla) {
      // eslint-disable-next-line no-new
      new JMultiSelect('#adminForm');
    } else if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('js-multiselect')) {
      if (Joomla.getOptions('js-multiselect').formName) {
        // eslint-disable-next-line no-new
        new JMultiSelect(`#${Joomla.getOptions('js-multiselect').formName}`);
      } else {
        // eslint-disable-next-line no-new
        new JMultiSelect('#adminForm');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(Joomla);
