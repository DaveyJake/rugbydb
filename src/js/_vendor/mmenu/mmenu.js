/*!
 * mmenu.js
 * mmenujs.com
 *
 * Copyright (c) Fred Heusschen
 * frebsite.nl
 *
 * License: CC-BY-NC-4.0
 * http://creativecommons.org/licenses/by-nc/4.0/
 */

// Core
import Mmenu from 'mmenu-js/dist/core/oncanvas/mmenu.oncanvas';
// Core add-ons
import offcanvas from 'mmenu-js/dist/core/offcanvas/mmenu.offcanvas';
import scrollBugFix from 'mmenu-js/dist/core/scrollbugfix/mmenu.scrollbugfix';
// Add-ons
import backButton from 'mmenu-js/dist/addons/backbutton/mmenu.backbutton';
import navbars from 'mmenu-js/dist/addons/navbars/mmenu.navbars';
import searchfield from 'mmenu-js/dist/addons/searchfield/mmenu.searchfield';
import setSelected from 'mmenu-js/dist/addons/setselected/mmenu.setselected';

/*!
 * mhead.js
 * mmenu.frebsite.nl/mhead
 *
 * Copyright (c) Fred Heusschen
 * www.frebsite.nl
 *
 * License: CC-BY-4.0
 * http://creativecommons.org/licenses/by/4.0/
 */
import Mhead from 'mhead-js/dist/core/mhead.core';

Mmenu.addons = {
    // Core add-ons
    offcanvas,
    scrollBugFix,
    // Add-ons
    backButton,
    navbars,
    searchfield,
    setSelected
};

// Global
window.Mmenu = Mmenu;
window.Mhead = Mhead;

// Export module
export { Mmenu, Mhead };
