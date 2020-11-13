/**
 * Quickly get the `textNode` if the nodeType is 3.
 *
 * @return {String} The text inside the HTML element node.
 */
const textNode = function() {
    return 3 === this.nodeType;
};

module.exports = { textNode };
