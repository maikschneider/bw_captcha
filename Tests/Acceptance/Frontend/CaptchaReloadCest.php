<?php

declare(strict_types=1);

namespace Blueways\BwCaptcha\Tests\Acceptance\Frontend;

use Blueways\BwCaptcha\Tests\Acceptance\Support\AcceptanceTester;

class CaptchaReloadCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->amOnPage('/captcha-form');
    }

    public function reloadButtonChangesCaptchaImage(AcceptanceTester $I): void
    {
        $I->wantTo('verify clicking reload button changes the captcha image');
        $I->waitForElement('.captcha img', 10);

        $originalSrc = $I->grabAttributeFrom('.captcha img', 'src');

        $I->click('a.captcha__reload');
        $I->wait(2);

        $newSrc = $I->grabAttributeFrom('.captcha img', 'src');
        $I->assertNotEquals($originalSrc, $newSrc, 'Captcha image src should change after reload');
    }

    public function reloadButtonAddsSpinAnimation(AcceptanceTester $I): void
    {
        $I->wantTo('verify clicking reload button triggers the spin animation');
        $I->waitForElement('.captcha img', 10);

        $I->click('a.captcha__reload');

        // The spin class is added immediately on click
        $I->seeElement('.captcha.captcha--spin');
    }
}
