// Prototype
NodeList.prototype.__proto__ = HTMLCollection.prototype.__proto__ = Array.prototype;

// Query
window.query = document.querySelector.bind(document);
window.queryAll = document.querySelectorAll.bind(document);
Node.prototype.query = function(selector) { return this.querySelector(selector) }
Node.prototype.queryAll = function(selector) { return this.querySelectorAll(selector) }
NodeList.prototype.query = function(selector) { return this.queryAll(selector)[0] }
NodeList.prototype.queryAll = function(selector) {
    let results = [];
    this.map(elem => results.push(...elem.first(selector)));
    return results;
}

// Utils
Node.prototype.sibling = function(query) { return this.siblings(query)[0] }
Node.prototype.siblings = function(query) {
	let elems = query ? this.parentElement.queryAll(query) : this.parentElement.children;
	return elems.filter(e => this != e);
}

// Index of node
Node.prototype.index = function() {
    let elem = this, i = 0;
    while(elem = elem.previousElementSibling)
        i++;
    return i;
}

// App
export function template(selector) {
    return query(selector).content.cloneNode(true);
}

// Admin panel
export function adminShortcut() {
    window.addEventListener("keypress", e => e.shiftKey && e.keyCode == 88 && (window.location.href = '/admin'), false);
}