window.BwCaptcha = {
  init() {
    this.initReloadButton();
    this.initImage();
    this.initAudioButton();
  },

  initReloadButton() {
    document.querySelectorAll('a.captcha__reload:not([data-initialized="true"])').forEach(element => {
      element.addEventListener('click', this.handleReloadClick);
      element.setAttribute('data-initialized', 'true');
    });
  },

  handleReloadClick(event) {
    event.preventDefault();
    const div = event.currentTarget.parentElement;
    div.classList.add('captcha--reloading', 'captcha--spin');
    const captchaUrl = event.currentTarget.dataset.url;
    event.currentTarget.previousElementSibling.setAttribute('src', `${captchaUrl}${/\?/.test(captchaUrl) ? '&' : '?'}now=${Date.now()}`);
    setTimeout(() => div.classList.remove('captcha--spin'), 400);
  },

  initImage() {
    document.querySelectorAll('.captcha img[aria-live="polite"]:not([data-initialized="true"])').forEach(element => {
      element.addEventListener('load', this.handleImageLoad);
      element.setAttribute('data-initialized', 'true');
    });
  },

  handleImageLoad(event) {
    const div = event.currentTarget.parentElement;
    div.classList.remove('captcha--reloading');
  },

  initAudioButton() {
    document.querySelectorAll('a.captcha__audio:not([data-initialized="true"])').forEach(element => {
      element.addEventListener('click', this.handleAudioClick);
      element.setAttribute('data-initialized', 'true');
    });
  },

  handleAudioClick(event) {
    event.preventDefault();
    const div = event.currentTarget.parentElement;
    window.captchaAudio = window.captchaAudio || new Audio();
    window.captchaAudio.addEventListener('ended', () => div.classList.remove('captcha--playing'));

    if (window.captchaAudio.paused) {
      div.classList.add('captcha--playing');
      window.BwCaptcha.playCaptchaAudio(div, event.currentTarget.dataset.url);
    } else {
      window.captchaAudio.pause();
      div.classList.remove('captcha--playing');
    }
  },

  playCaptchaAudio(div, audioUrl) {
    const img = div.querySelector('img');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const data = new FormData();

    canvas.width = img.naturalWidth;
    canvas.height = img.naturalHeight;
    ctx.drawImage(img, 0, 0);
    data.append('captchaDataUrl', canvas.toDataURL());

    fetch(audioUrl, {method: 'POST', body: data})
      .then(response => response.blob())
      .then(blob => {
        window.captchaAudio.src = window.URL.createObjectURL(blob);
        window.captchaAudio.play();
      });
  }
};

window.BwCaptcha.init();
