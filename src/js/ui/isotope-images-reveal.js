import { _ } from '../utils/globals';
/**
 * jQuery plugin extension.
 *
 * @since 1.0.0
 */

/**
 * Programmatically reveal images as they become available.
 *
 * @since 1.0.0
 *
 * @param {jQuery}        $items     Array of elements.
 * @param {string|number} sortValue  Sort value.
 * @param {bool}          background Load as a background value.
 */
const isotopeImagesReveal = function( $items, sortValue, background ) {
    // Isotope instance
    const iso = this.data( 'isotope' );

    // `childNode` targets
    const itemSelector = iso.options.itemSelector;

    // hide by default
    $items.hide();

    // append to container
    this.append( $items );

    // For background images?
    if ( ! _.isEmpty( background ) ) {
        const element = background;
        background = { background: element };
    } else {
        background = '';
    }

    // show progress load
    $items.imagesLoaded( background ).progress( function( imgLoad, image ) {
        // get item
        // image is imagesLoaded class, not <img>, <img> is image.img
        const $item = ! image.img ? itemSelector : $( image.img ).parents( itemSelector );

        // un-hide item
        $item.show();

        // isotope does its thing
        iso.appended( $item );

        // sortBy
        if ( ! _.isEmpty( sortValue ) ) {
            iso.arrange({ sortBy: sortValue });
        }

        // Selector `class` for visible `$item`
        $item.is( ':visible' ) ? $item.addClass( 'item' ) : $item.show(); // eslint-disable-line

        // BUGFIX: Prevent Vertical-Line Load
        iso.layout();
    });

    return this;
};

module.exports = { isotopeImagesReveal };
