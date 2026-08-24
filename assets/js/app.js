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
    document.documentElement.style.setProperty('--header-h', header.offsetHeight + 'px');
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

const heroSlides = document.querySelectorAll('[data-hero-slide]');
if (heroSlides.length > 1) {
  let current = 0;
  setInterval(() => {
    heroSlides[current].classList.add('opacity-0');
    current = (current + 1) % heroSlides.length;
    heroSlides[current].classList.remove('opacity-0');
  }, 5000);
}

try {
  if (window.gsap) {
    const rightCard = document.querySelector('[data-hero-right]');
    if (rightCard) {
      gsap.from(rightCard, { autoAlpha: 0, x: 40, duration: 0.7, delay: 0.3, ease: 'power3.out' });
    }
  }
} catch (e) {
  document.querySelectorAll('[data-nav-anim], [data-hero-right]')
    .forEach((el) => { el.style.opacity = ''; el.style.visibility = ''; });
}
