/**
 * Get ISO-formatted date.
 *
 * @static
 *
 * @namespace Date
 *
 * @return {string} The date in `YYYY-MM-DD` format.
 */
Date.prototype.Ymd = function() {
    const m = this.getMonth() + 1,
          d = this.getDate();

    return [
        this.getFullYear(),
        ( 9 < m ? '' : '0' ) + m,
        ( 9 < d ? '' : '0' ) + d
    ].join( '-' );
};

/**
 * Return `AM` or `PM` based on current `Date` instance.
 *
 * @namespace Date
 *
 * @return {string} AM or PM
 */
Date.prototype.getMeridian = function() {
    return 12 < this.getHours() ? 'PM' : 'AM';
};

module.exports = Date;
