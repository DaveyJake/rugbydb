/**
 * This file contains all custom defined types used throughout the app.
 *
 * @since 2.0.0
 */

/**
 * The type of all primitives.
 *
 * @since 2.0.0
 *
 * @typedef {(Array.<any> | boolean | number | object | string)} Primitives
 */

/**
 * The primary type that controls all AJAX request calls to the RESTful API.
 *
 * @since 2.0.0
 *
 * @typedef  RugbyRequest
 * @type     {object}
 * @property {string}  route             Slug of the requested post type.
 * @property {string}  nonce             Generated nonce key.
 * @property {boolean} [collection=true] Is the request for multiple items? Default true.
 * @property {string}  [grid=#grid]      The grid attribute selector. Default `#grid`.
 * @property {number}  [page=0]          Page number to retrieve.
 * @property {number}  [perPage=0]       Number of posts to display.
 * @property {number}  [postId=0]        Post ID.
 * @property {string}  [postName='']     Post slug.
 * @property {string}  [venue='']        Venue slug.
 */
