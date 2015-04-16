[![banner](demo/imgs/banner.jpg)](#)

This Bootstrap theme is an easy way to use the new Material Design guidelines by Google in your Bootstrap 3 based application.
Just include the theme right after the Bootstrap CSS and include the javascript at the end of your document, everything will be converted to Material Design (paper) style.

This theme is in early development and is not ready for production.

Check out [the demo at this link](http://fezvrasta.github.io/bootstrap-material-design/) (could be not even with the master branch).

## How to install

    BOWER:       bower install bootstrap-material-design --save
    GEMS:        gem install bootstrap-material-design

### LESS & SASS

We decided to merge the SASS fork in to the main repository instead of having two seperate ones, [#40](https://github.com/FezVrasta/bootstrap-material-design/issues/40). The default grunt
task will compile the LESS source. To use the SASS base use the SCSS task ```grunt scss```.

**NB** The LESS and SASS bases should reflect each other, right now this project is unstable and under heavy development
so this might not be true before the first stable release.

## Getting started

Navigate to the `template/` folder in this repository, and you will see the `index.html` file, which has the CSS include statements, in the `head` section and the JS includes just before `body` section closes.
You need to copy the `material/` folder to the root of your project, ensuring that all the files in your project can access the files through the relative URL, supplied in the CSS and the JS includes.

## Todo

- Morphing icons
- [Icons/grids/chips to card/fullscreen transitions](http://www.polymer-project.org/components/core-animated-pages/demo.html)
- [Headers](http://www.polymer-project.org/components/core-header-panel/demo.html)
- [Icon button](http://www.polymer-project.org/components/paper-icon-button/demo.html)
- [Tabs](http://www.polymer-project.org/components/paper-tabs/demo.html)
- [Toggle buttons](http://www.polymer-project.org/components/paper-toggle-button/demo.html)

I'll try to write every component without the need of Javascript but just CSS, and use JS only if strictly needed.
More "todo" things can be found in the ISSUES of this repository.

# Support me

If you like this project you may support me by donating something on Gittip, starring this repository or reporting bugs and ideas in the issue section.

[![gittip](screenshots/gittip-button.jpg)](https://www.gratipay.com/FezVrasta/)
[![issues](screenshots/issues-button.jpg)](https://github.com/FezVrasta/bootstrap-material-design/issues)

# Contribute

Please see the [contrib](CONTRIBUTING.md) file.


# Documentation

Material Design for Bootstrap provides some additional stuff to get the best from Material Design.

### Variations:

There are 17 additional color variations (in addition to the classic 4 variations) for buttons, inputs, checkboxes, radios, alerts, navbars, tabs, labels, paginations, progress bars and more.
They can be used by adding the class suffix `-material-color` to the desired element and replacing `color` with the desired one.

Example:

    <button class="btn btn-material-deeppurple">Deep purple button</button>

These colors are taken from the Material Design color palette and are reported below:

![palette](screenshots/palette.jpg)

### Buttons:

Add `.btn-flat` to a button to make it flat, without shadows.
Add `.btn-raised` to a button to add a permanent shadow to it.

### Inputs:

Add `.floating-label` to an input field with a `placeholder` to transform the placeholder in a floating label.

Remember to use the proper HTML markup to get radio and checkboxes styled correctly (choose between *radio* or *checkbox*):

    <div class="radio/checkbox radio-primary">
        <label>
            <input type="radio/checkbox" checked>
            Option one is this
        </label>
    </div>

### Icons:

Material Design for Bootstrap includes 490 original Material Design icons!
These icons are extracted from the original Google sources and are licensed under the BSD license.
They are provided as an iconic and easy to use font.

Variations are available for every icon, including the original Bootstrap icons.

The syntax to add a Material icon is:

     <i class="icon icon-material-favorite"></i>

# Plugins

Material Design for Bootstrap comes with styling support for various external scripts:

### SnackbarJS

Create snackbars and toasts with [SnackbarJS plugin](https://github.com/FezVrasta/snackbarjs). The default toast style is the squared one (snackbar style). If you like to use the rounded style (toast style), please add the `toast` class to the `style` option of SnackbarJS.

### RipplesJS

This is part of Material Design for Bootstrap project and is a plain Javascript script which creates the ripple effect on click of the defined elements.
At the moment RipplesJS does not have its own repository but it will probably have one in the future.

### noUiSlider

Make cross-browser sliders and get them styled with Material Design thanks to the support provided by this theme.
Read more about [noUiSlider here](http://refreshless.com/nouislider/)

### Selectize.js

Transform select and multi select inputs in advanced text inputs. Material Design for BS provides a fulle replacement of the plugin's CSS, don't include it so.
Read more about [selectize.js](http://brianreavis.github.io/selectize.js/)


# Compatibility

Currently Material Design for Bootstrap supports Google Chrome (tested v37+), Mozilla Firefox (tested 30+), and Internet Explorer (tested 11+). Mobile browsers are not currently tested but it may work.

