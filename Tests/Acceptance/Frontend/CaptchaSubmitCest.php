<?php

declare(strict_types=1);

namespace Blueways\BwCaptcha\Tests\Acceptance\Frontend;

use Blueways\BwCaptcha\Tests\Acceptance\Support\AcceptanceTester;

class CaptchaSubmitCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->amOnPage('/captcha-form');
        $I->waitForElement('.captcha img', 10);
    }

    public function wrongCaptchaInputShowsError(AcceptanceTester $I): void
    {
        $I->wantTo('verify submitting a wrong captcha value shows an error');

        $I->fillField('input[id*="captcha"]', 'WRONG');
        $I->click('button[type="submit"]');
        $I->wait(2);

        $I->see('not valid');
    }

    public function emptyCaptchaInputShowsError(AcceptanceTester $I): void
    {
        $I->wantTo('verify submitting an empty captcha value shows an error');

        // Remove the required attribute so we can submit the form with empty captcha
        $I->executeJS("document.querySelector('input[id*=\"captcha\"]').removeAttribute('required');");
        $I->click('button[type="submit"]');
        $I->wait(2);

        $I->see('empty');
    }

    public function successfulCaptchaSubmitWithFixedPassword(AcceptanceTester $I): void
    {
        $I->wantTo('verify submitting the correct captcha value succeeds');

        // Inject a known captcha phrase into the frontend user session via database
        $knownPhrase = 'TEST1';
        $lifetime = time() + 3600;
        $captchaPhrases = [$lifetime => $knownPhrase];
        $sessionData = serialize(['captchaPhrases' => $captchaPhrases]);

        $I->updateInDatabase('fe_sessions', ['ses_data' => $sessionData], ['1' => '1']);

        $I->fillField('input[id*="captcha"]', $knownPhrase);
        $I->click('button[type="submit"]');
        $I->wait(2);

        // After successful submit, the form should not show captcha validation error
        $I->dontSee('not valid');
    }
}
