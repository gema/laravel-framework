// App
export function moveTo(slider, index) {
  // Change active Dot
  const dots = slider.query('.dots');
  dots.query('.active').classList.remove('active');
  dots.children[index].classList.add('active');

  // Add the translate
  slider.query('ul').style = `transform: translateX(-${index * 100}%);`;
  slider.setAttribute('page', index);
}

export function move(slider) {
  const dots = slider.query('.dots');
  let index = dots.query('.active').index();

  index += 1;
  if (index === dots.children.length) { index = 0; }

  moveTo(slider, index);
}

export function start(slider) {
  const autoScroll = slider.getAttribute('auto-scroll');

  if (autoScroll) {
    const interval = slider.getAttribute('interval');
    if (interval) { clearInterval(interval); }

    slider.setAttribute('interval', setInterval(() => {
      move(slider);
    }, autoScroll));
  }
}

export function swipe(slider, direction = 1) {
  const dots = slider.query('.dots');
  let index = dots.query('.active').index();

  if ((index === 0 && direction < 0)
      || (index === dots.children.length - 1 && direction > 0)) {
    return;
  }

  index += direction;
  moveTo(slider, index);
}

export function init() {
  queryAll('.flex-slider').forEach(elem => {
    start(elem);
    elem.setAttribute('page', 0);

    // Dots
    elem.queryAll('.dots > li').forEach(dot => {
      dot.addEventListener('click', () => {
        // Index of this element in parent
        const index = dot.index();
        const slider = dot.closest('.flex-slider');

        // Restart slider interval
        start(slider);

        moveTo(slider, index);
      });
    });

    // Arrows
    elem.queryAll('.arrows > .arrow').forEach(arrow => {
      arrow.addEventListener('click', () => {
        // Index of this element in parent
        const slider = arrow.closest('.flex-slider');
        const direction = arrow.getAttribute('direction');
        const page = slider.getAttribute('page');
        const pages = slider.queryAll('ul.pages > li').length;

        // Restart slider interval
        start(slider);

        switch (direction) {
          case 'left':
            if (page > 0) {
              moveTo(slider, parseInt(page, 10) - 1);
            } else {
              moveTo(slider, pages - 1);
            }
            break;
          default:
          case 'right':
            if (page < pages - 1) {
              moveTo(slider, parseInt(page, 10) + 1);
            } else {
              moveTo(slider, 0);
            }
            break;
        }
      });
    });

    // Touch
    elem.addEventListener('swipe', e => {
      if (e.detail.x) { swipe(elem, e.detail.x); }
    });
  });
}
