/**
* Module: @blueways/bw-captcha/Backend/FormEditor/CaptchaViewModel.js
*/
import * as Helper from '@typo3/form/backend/form-editor/helper.js';

export function bootstrap(formEditorApp) {
  Helper.bootstrap(formEditorApp);

  formEditorApp.getPublisherSubscriber().subscribe(
    'view/stage/abstract/render/template/perform',
    (topic, args) => {
      if (args[0].get('type') === 'Captcha') {
        formEditorApp.getViewModel().getStage().renderSimpleTemplateWithValidators(args[0], args[1]);
      }
    }
  );
}
