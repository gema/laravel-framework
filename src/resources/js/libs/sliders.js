// App
export function init() {
    queryAll('.flex-slider').forEach(elem => {
        start(elem);
        elem.setAttribute('page', 0);

        // Dots
        elem.queryAll('.dots > li').forEach(dot => {
            dot.addEventListener('click', e => {
                // Index of this element in parent
                let index = dot.index();
                let slider = dot.closest('.flex-slider');

                // Restart slider interval
                start(slider);

                moveTo(slider, index);
            })
        });

        // Arrows
        elem.queryAll('.arrows > .arrow').forEach(arrow => {
            arrow.addEventListener('click', e => {
                // Index of this element in parent
                let slider = arrow.closest('.flex-slider');
                let direction = arrow.getAttribute('direction');
                let page = slider.getAttribute('page');
                let pages = slider.queryAll('ul.pages > li').length;

                // Restart slider interval
                start(slider);

                switch (direction) {
                    case 'left':
                        if (page > 0)
                            moveTo(slider, parseInt(page) - 1);
                        else
                            moveTo(slider, pages - 1);
                        break;
                    case 'right':
                        if (page < pages - 1) {
                            moveTo(slider, parseInt(page) + 1);
                        } else {
                            moveTo(slider, 0);
                        }
                        break;
                }
            })
        });

        // Touch
        elem.addEventListener('swipe', e => {
            if (e.detail.x)
                swipe(elem, e.detail.x);
        });
    });
}

export function start(slider) {
    let autoScroll = slider.getAttribute('auto-scroll');

    if (autoScroll) {
        let interval = slider.getAttribute('interval');
        if (interval)
            clearInterval(interval);

        slider.setAttribute('interval', setInterval(e => {
            move(slider);
        }, autoScroll));
    }
}

export function move(slider) {
    let dots = slider.query('.dots');
    let arrow = slider.query('.arrow');
    let index = dots.query('.active').index();

    index++;
    if (index == dots.children.length)
        index = 0;

    moveTo(slider, index);
}

export function swipe(slider, direction = 1) {
    let dots = slider.query('.dots');
    let index = dots.query('.active').index();

    if ((index == 0 && direction < 0) || (index == dots.children.length - 1 && direction > 0))
        return;

    index += direction;
    moveTo(slider, index);
}

export function moveTo(slider, index) {
    // Change active Dot
    let dots = slider.query('.dots');
    dots.query('.active').classList.remove('active');
    dots.children[index].classList.add('active');

    // Add the translate
    slider.query('ul').style = "transform: translateX(-" + index * 100 + "%);";
    slider.setAttribute('page', index);
}