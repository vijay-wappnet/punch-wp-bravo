import jQuery from 'jquery';

/**
 * jQuery 4 removed `jQuery.type()`, but @accessible-slick still calls it to
 * read its `responsive` option and in `slickSetOption`. Restore it with the
 * jQuery 3 behaviour ("array", "object", "string", "null", ...).
 * Import this before any plugin that relies on it.
 */
if (typeof jQuery.type !== 'function') {
  jQuery.type = (obj) => (obj == null
    ? String(obj)
    : Object.prototype.toString.call(obj).slice(8, -1).toLowerCase());
}

export default jQuery;
