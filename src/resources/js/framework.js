// Prototype
Object.setPrototypeOf(NodeList.prototype, Array.prototype);
Object.setPrototypeOf(HTMLCollection.prototype, Array.prototype);

// Query
window.query = document.querySelector.bind(document);
window.queryAll = document.querySelectorAll.bind(document);
Node.prototype.query = function query(selector) { return this.querySelector(selector); };
Node.prototype.queryAll = function queryAll(selector) { return this.querySelectorAll(selector); };
NodeList.prototype.query = function query(selector) { return this.queryAll(selector)[0]; };
NodeList.prototype.queryAll = function queryAll(selector) {
  const results = [];
  this.map((elem) => results.push(...elem.first(selector)));
  return results;
};

// Utils
Node.prototype.sibling = function sibling(query) { return this.siblings(query)[0]; };
Node.prototype.siblings = function siblings(query) {
  const elems = query ? this.parentElement.queryAll(query) : this.parentElement.children;
  return elems.filter((e) => this !== e);
};

// Index of node
Node.prototype.index = function index() {
  let elem = this;
  let i = 0;
  while (elem) { elem = elem.previousElementSibling; i++; }
  return i;
};

// App
export function template(selector) {
  return query(selector).content.cloneNode(true);
}

// Admin panel
export function adminShortcut() {
  window.addEventListener('keypress', (e) => e.shiftKey && e.keyCode === 88 && (window.location.href = '/admin'), false);
}
