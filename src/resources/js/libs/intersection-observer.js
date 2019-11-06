// Interception observer
export function init() {
    queryAll(".observable").forEach(observable => {
        let threshold = parseFloat(observable.getAttribute('threshold'));

        let revealContentObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.dispatchEvent(new CustomEvent('observed'));
                    revealContentObserver.unobserve(entry.target);
                }
            });
        }, { threshold: threshold });

        revealContentObserver.observe(observable);
    });
}