// Interception observer
// eslint-disable-next-line import/prefer-default-export
export function init() {
  queryAll('.observable, [observable]').forEach(observable => {
    let status = false;
    const threshold = parseFloat(observable.getAttribute('threshold') || 1);
    const once = observable.getAttribute('once') !== null;

    const revealContentObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !status) {
          status = true;
          entry.target.dispatchEvent(new CustomEvent('observed', entry));
          if (once) {
            revealContentObserver.unobserve(entry.target);
          }
        } else if (!entry.isIntersecting && status) {
          status = false;
          entry.target.dispatchEvent(new CustomEvent('unobserved', entry));
        }
      });
    }, { threshold });

    revealContentObserver.observe(observable);
  });
}
