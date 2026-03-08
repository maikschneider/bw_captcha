<?php

declare(strict_types=1);

namespace Blueways\BwCaptcha\Tests\Acceptance\Frontend;

use Blueways\BwCaptcha\Tests\Acceptance\Support\AcceptanceTester;

class CaptchaAudioCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->amOnPage('/captcha-form');
        $I->waitForElement('.captcha img', 10);
    }

    public function audioButtonIsVisible(AcceptanceTester $I): void
    {
        $I->wantTo('verify the audio button is displayed on the captcha form');
        $I->seeElement('a.captcha__audio');
        $I->seeElement('a.captcha__audio svg');
    }

    public function audioButtonHasCorrectAttributes(AcceptanceTester $I): void
    {
        $I->wantTo('verify the audio button has proper accessibility attributes');
        $I->seeElement('a.captcha__audio[role="button"]');
        $dataUrl = $I->grabAttributeFrom('a.captcha__audio', 'data-url');
        $I->assertStringContainsString('type=3414', $dataUrl);
    }

    public function audioButtonHasSoundAndMuteIcons(AcceptanceTester $I): void
    {
        $I->wantTo('verify the audio button has both sound and mute SVG icons');
        $I->seeElement('a.captcha__audio .captcha__audio__sound');
        $I->seeElement('a.captcha__audio .captcha__audio__mute');
    }

    public function clickingAudioButtonTriggersPlayback(AcceptanceTester $I): void
    {
        $I->wantTo('verify clicking the audio button triggers audio playback');

        // Click the audio button
        $I->click('a.captcha__audio');
        $I->wait(1);

        // When audio is playing, the captcha container should have the playing class
        $I->seeElement('.captcha.captcha--playing');
    }

    public function audioButtonClickSendsFetchRequest(AcceptanceTester $I): void
    {
        $I->wantTo('verify clicking the audio button sends a POST request to the audio endpoint');

        // Set up a fetch spy
        $I->executeJS("
            window.__audioFetchCalled = false;
            const originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (url && url.toString().includes('type=3414') && options && options.method === 'POST') {
                    window.__audioFetchCalled = true;
                }
                return originalFetch.apply(this, arguments);
            };
        ");

        $I->click('a.captcha__audio');
        $I->wait(3);

        $fetchCalled = $I->executeJS('return window.__audioFetchCalled;');
        $I->assertTrue($fetchCalled, 'Audio fetch request should have been made');
    }
}
