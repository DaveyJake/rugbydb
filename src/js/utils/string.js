/**
 * Quickly get the `textNode` if the nodeType is 3.
 *
 * @return {string} The text inside the HTML element node.
 */
const textNode = function() {
  return 3 === this.nodeType;
};

export { textNode };
