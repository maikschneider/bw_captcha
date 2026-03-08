<?php

declare(strict_types=1);

namespace Blueways\BwCaptcha\Tests\Acceptance\Frontend;

use Blueways\BwCaptcha\Tests\Acceptance\Support\AcceptanceTester;

class CaptchaAppearanceCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->amOnPage('/captcha-form');
    }

    public function captchaImageIsVisible(AcceptanceTester $I): void
    {
        $I->wantTo('verify the captcha image is displayed on the form page');
        $I->seeElement('.captcha img');
    }

    public function captchaImageHasCorrectAttributes(AcceptanceTester $I): void
    {
        $I->wantTo('verify the captcha image has proper attributes');
        $I->seeElement('.captcha img[aria-live="polite"]');
        $I->seeElement('.captcha img[loading="lazy"]');
    }

    public function captchaImageLoadsFromMiddleware(AcceptanceTester $I): void
    {
        $I->wantTo('verify the captcha image src points to the captcha middleware');
        $src = $I->grabAttributeFrom('.captcha img', 'src');
        $I->assertStringContainsString('type=3413', $src);
    }

    public function captchaInputFieldIsVisible(AcceptanceTester $I): void
    {
        $I->wantTo('verify the captcha text input field is displayed');
        $I->seeElement('.captcha ~ input[type="text"], .captcha + input, input[id*="captcha"]');
    }

    public function refreshButtonIsVisible(AcceptanceTester $I): void
    {
        $I->wantTo('verify the refresh button is displayed');
        $I->seeElement('a.captcha__reload');
        $I->seeElement('a.captcha__reload svg');
    }

    public function refreshButtonHasCorrectAttributes(AcceptanceTester $I): void
    {
        $I->wantTo('verify the refresh button has proper accessibility attributes');
        $I->seeElement('a.captcha__reload[role="button"]');
        $dataUrl = $I->grabAttributeFrom('a.captcha__reload', 'data-url');
        $I->assertStringContainsString('type=3413', $dataUrl);
    }
}
