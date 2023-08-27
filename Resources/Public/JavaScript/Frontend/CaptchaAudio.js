function onClick() {
	const div = this.parentElement;
	window.captchaAudio = window.captchaAudio ? window.captchaAudio : new Audio();
	window.captchaAudio.addEventListener('ended', () => div.classList.remove('captcha--playing'));
	if (window.captchaAudio.paused) {
		div.classList.add('captcha--playing');
		const img = div.querySelector('img');
		const c = document.createElement('canvas');
		const ctx = c.getContext('2d');
		const data = new FormData();
		c.width = img.naturalWidth;
		c.height = img.naturalHeight;
		ctx.drawImage(img, 0, 0);
		data.append('captchaDataUrl', c.toDataURL());
		fetch('{f:uri.page(pageType:3414)}', {
			method: 'POST',
			body: data
		}).then(response => response.blob()).then(blob => {
			window.captchaAudio.src = window.URL.createObjectURL(blob);
			window.captchaAudio.play();
		});
	} else {
		window.captchaAudio.pause();
		div.classList.remove('captcha--playing')
	}
	return false;
}