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
        # Show reload button
        refreshButton =
        
        # The length of the captcha
        length =
        
        # The charset of the captcha
        charset =
        
        # The width of the image
        width =
        
        # The height of the image
        height =
        
        # Custom font file(s) to use (comma-separated)
        fontFiles =
        
        # Text color (e.g. 255,0,0)
        textColor =
        
        # Line color (e.g. 0,0,0)
        lineColor =
        
        # Background color (e.g. 255,255,255)
        backgroundColor =
        
        # Distortion
        distortion =
        
        # The maximum number of lines to draw in front of
        maxFrontLines =
        
        # The maximum number of lines to draw behind
        maxBehindLines =
        
        # The maximum angle of char
        maxAngle =
        
        # The maximum offset of char
        maxOffset =
        
        # Is the interpolation enabled?
        interpolation =
        
        # Ignore all effects
        ignoreAllEffects =
        
    }
}
```

## Migration from version 2.x to 3.x 

The generation of the captcha moved to a middleware, which solves a lot of caching issues. Therefore, adjustments to the form element partial have been made. If you've modified the partial, you need to update the image tag and refresh button link.

**tl;dr**:

* Check out the [new captcha partial](https://github.com/maikschneider/bw_captcha/blob/master/Resources/Private/Frontend/Partials/Captcha.html)
* Reload button is enabled by default (can be disabled via `plugin.tx_bwcaptcha.settings.refreshButton`)
* You can re-enable the page cache, if disabled it because of this element 

## Troubleshooting

### Refresh button not working

If your site is configured to use trailing slashes, the refresh url cannot be resolved. A simple fix is to add a setting for the pageType 3413, e.g.:

```yaml
routeEnhancers:
  PageTypeSuffix:
    type: PageType
    default: /
    index: index
    map:
      /: 0
      .captcha: 3413
```

## Contribute

This extension was made by Maik Schneider: Feel free to contribute!

* [Github-Repository](https://github.com/maikschneider/bw_captcha)

Thanks to [blueways](https://www.blueways.de/) and [XIMA](https://www.xima.de/)!
