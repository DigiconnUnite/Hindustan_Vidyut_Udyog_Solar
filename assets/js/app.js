document.getElementById('nav-toggle')?.addEventListener('click', () => {
  document.getElementById('nav-mobile')?.classList.toggle('hidden');
});

const ribbon = document.getElementById('header-ribbon');
if (ribbon) {
  const header = document.getElementById('site-header');
  const spacer = document.getElementById('header-spacer');
  const syncSpacer = () => {
    // header height changes with the ribbon; keep the spacer matched to it
    if (spacer) spacer.style.height = header.offsetHeight + 'px';
  };
  const onScroll = () => {
    const hide = window.scrollY > 40;
    ribbon.classList.toggle('grid-rows-[0fr]', hide);
    ribbon.classList.toggle('grid-rows-[1fr]', !hide);
    ribbon.classList.toggle('opacity-0', hide);
    syncSpacer();
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', syncSpacer, { passive: true });
  ribbon.addEventListener('transitionend', syncSpacer);
  onScroll();
}

try {
  if (window.gsap) {
    const navEls = document.querySelectorAll('[data-nav-anim]');
    const bg = document.querySelector('[data-hero-bg]');
    const title = document.querySelector('[data-hero-title]');
    const heroEls = document.querySelectorAll('[data-hero-anim]');
    const stats = document.querySelectorAll('[data-hero-stat]');
    const leftCard = document.querySelector('[data-hero-left]');
    const rightCard = document.querySelector('[data-hero-right]');

    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    if (navEls.length) {
      tl.from(navEls, { opacity: 0, y: -20, duration: 0.6, stagger: 0.08, clearProps: 'opacity,transform' }, 0);
    }
    if (bg) {
      tl.to(bg, { scale: 1, duration: 1.8, ease: 'power2.out' }, 0);
    }
    if (title) {
      tl.from(title, { autoAlpha: 0, y: 40, skewX: 3, duration: 0.9 }, 0.25);
    }
    if (heroEls.length) {
      tl.from(heroEls, { autoAlpha: 0, y: 30, duration: 0.7, stagger: 0.15 }, 0.5);
    }
    if (stats.length) {
      tl.from(stats, { autoAlpha: 0, y: 24, scale: 0.9, duration: 0.6, stagger: 0.12, ease: 'back.out(1.7)' }, 0.8);
    }
    if (leftCard) {
      tl.from(leftCard, { autoAlpha: 0, x: -40, duration: 0.7 }, 0.9);
    }
    if (rightCard) {
      tl.from(rightCard, { autoAlpha: 0, x: 40, duration: 0.7 }, 0.95);
    }
  }
} catch (e) {
  document.querySelectorAll('[data-nav-anim], [data-hero-title], [data-hero-anim], [data-hero-stat], [data-hero-left], [data-hero-right]')
    .forEach((el) => { el.style.opacity = ''; el.style.visibility = ''; });
}
