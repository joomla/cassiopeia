/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
document.addEventListener('DOMContentLoaded', function () {
  var allMenus = document.querySelectorAll('ul.mod-menu_metismenu');
  allMenus.forEach(function (menu) {
    // eslint-disable-next-line no-new, no-undef
    var mm = new MetisMenu(menu, {
      triggerElement: 'button.mm-toggler'
    }).on('shown.metisMenu', function (event) {
      window.addEventListener('click', function mmClick(e) {
        if (!event.target.contains(e.target)) {
          mm.hide(event.detail.shownElement);
          window.removeEventListener('click', mmClick);
        }
      });
    });
  });
});
