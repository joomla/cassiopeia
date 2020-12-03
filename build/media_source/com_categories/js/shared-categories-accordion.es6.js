/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  'use strict';

  if (!Joomla || !Joomla.Text) {
    throw new Error('core.js was not properly initialised');
  }

  // Selectors used by this script
  const buttonsSelector = '[id^=category-btn-]';

  /**
   * Handle the category toggle button click event
   * @param event
   */
  const handleCategoryToggleButtonClick = ({ currentTarget }) => {
    const button = currentTarget;
    const icon = button.querySelector('span');

    // Toggle icon class
    icon.classList.toggle('icon-plus');
    icon.classList.toggle('icon-minus');

    // Toggle aria label
    const ariaLabel = button.getAttribute('aria-label');
    button.setAttribute(
      'aria-label',
      (
        ariaLabel === Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES') ? Joomla.Text._('JGLOBAL_COLLAPSE_CATEGORIES') : Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES')
      ),
    );
  };

  /**
   * Script boot
   */
  const onBoot = () => {
    const buttons = [].slice.call(document.querySelectorAll(buttonsSelector));
    buttons.forEach((button) => {
      button.addEventListener('click', handleCategoryToggleButtonClick);
    });

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(Joomla);
