// Swipeable
// eslint-disable-next-line import/prefer-default-export
export function init() {
  queryAll('.swipeable').forEach(elem => {
    let startTouch;
    elem.addEventListener('touchstart', e => {
      [startTouch] = e.changedTouches;
    }, {
      passive: true,
    });
    elem.addEventListener('touchend', e => {
      let [dx, dy] = [
        e.changedTouches[0].clientX - startTouch.clientX,
        e.changedTouches[0].clientY - startTouch.clientY,
      ];

      dx = Math.abs(dx) > 120 ? (dx > 0 ? -1 : 1) : 0;
      dy = Math.abs(dy) > 120 ? (dy > 0 ? -1 : 1) : 0;

      if (dx | dy) {
        elem.dispatchEvent(new CustomEvent('swipe', {
          detail: {
            x: dx,
            y: dy,
          },
        }));
      }
    }, {
      passive: true,
    });
  });
}
