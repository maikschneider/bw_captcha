<?php

declare(strict_types=1);

namespace Blueways\BwCaptcha\Tests\Acceptance\Backend;

use Blueways\BwCaptcha\Tests\Acceptance\Support\AcceptanceTester;

class FormEditorCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->loginAsAdmin();
    }

    public function captchaElementExistsInFormEditor(AcceptanceTester $I): void
    {
        $I->wantTo('verify the Captcha form element is available in the backend form editor');

        // Navigate to the Form module
        $I->click('//a[@data-modulemenu-identifier="web_FormFormbuilder"]');
        $I->wait(2);
        $I->switchToContentFrame();

        // Create a new form or open an existing one
        $I->waitForElement('button.btn', 10);
        $I->click('Create new form');
        $I->switchToIFrame();
        $I->waitForElement('.modal', 10);

        // Fill in form name and proceed
        $I->fillField('input[id="simpleInput"]', 'Test Form');
        $I->click('Next');
        $I->wait(1);
        $I->click('Next');
        $I->wait(2);

        $I->switchToContentFrame();
        $I->waitForElement('.form-editor', 30);

        // Open the "new element" panel to see available form elements
        $I->click('.t3-form-btn-new-element');
        $I->wait(1);

        // Verify the Captcha element is listed in the available elements
        $I->see('Captcha');
    }
}
