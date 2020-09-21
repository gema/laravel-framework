// Interception observer
// eslint-disable-next-line import/prefer-default-export
export function init() {
  queryAll('.observable').forEach(observable => {
    const threshold = parseFloat(observable.getAttribute('threshold'));

    const revealContentObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.dispatchEvent(new CustomEvent('observed'));
          revealContentObserver.unobserve(entry.target);
        }
      });
    }, { threshold });

    revealContentObserver.observe(observable);
  });
}
