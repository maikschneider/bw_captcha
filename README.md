# Captcha extension for TYPO3 form

This extension adds a captcha element for the TYPO3 form component. The captcha generation uses [Gregwar/Captcha](https://github.com/Gregwar/Captcha), **no Google or 3rd party** includes.  

![Frontend Captcha example](Documentation/Images/Example.png)

## Install

* ```composer require blueways/bw-captcha```
* Activate extension
* Include TypoScript template

## Usage

Add the captcha element via Form Editor to your form or directly to your yaml form. 

### Via Form Editor

![Captcha via Form Editor](Documentation/Images/Example2.jpg)

### Or manual configuration

```yaml
renderables:
     -
        type: Captcha
        identifier: captcha
        label: Captcha
        properties:
          fluidAdditionalAttributes:
            required: required
```

### Configuration

To modify the captcha output, you can use the following TypoScript **constants**:

```typo3_typoscript
plugin.tx_bwcaptcha {
    settings {
        # show reload button
        refreshButton = 1
    }
}
```
