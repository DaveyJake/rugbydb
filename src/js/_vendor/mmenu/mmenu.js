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
import screenReader from 'mmenu-js/dist/core/screenreader/mmenu.screenreader';
import scrollBugFix from 'mmenu-js/dist/core/scrollbugfix/mmenu.scrollbugfix';
// Add-ons
import autoHeight from 'mmenu-js/dist/addons/autoheight/mmenu.autoheight';
import backButton from 'mmenu-js/dist/addons/backbutton/mmenu.backbutton';
import dropdown from 'mmenu-js/dist/addons/dropdown/mmenu.dropdown';
import fixedElements from 'mmenu-js/dist/addons/fixedelements/mmenu.fixedelements';
import keyboardNavigation from 'mmenu-js/dist/addons/keyboardnavigation/mmenu.keyboardnavigation';
import lazySubmenus from 'mmenu-js/dist/addons/lazysubmenus/mmenu.lazysubmenus';
import navbars from 'mmenu-js/dist/addons/navbars/mmenu.navbars';
import searchfield from 'mmenu-js/dist/addons/searchfield/mmenu.searchfield';
import setSelected from 'mmenu-js/dist/addons/setselected/mmenu.setselected';
// Wrappers
import wordpress from 'mmenu-js/dist/wrappers/wordpress/mmenu.wordpress';

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
    screenReader,
    scrollBugFix,
    // Add-ons
    autoHeight,
    backButton,
    dropdown,
    fixedElements,
    keyboardNavigation,
    lazySubmenus,
    navbars,
    searchfield,
    setSelected
};

// Wrappers
Mmenu.wrappers = { wordpress };

// Global
window.Mmenu = Mmenu;
window.Mhead = Mhead;

// Export module
module.exports = { Mmenu, Mhead };
