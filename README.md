Silverstripe Theme Framework
=============

A default set of tools to create themes for Silverstripe with LESS.

SiteConfig and Page extensions
-------------
When adding this module, the ThemeSiteConfigExtension is activated on your SiteConfig.
It will create a new theme tab where you can configure your theme.

You can configure the base, primary and secondary colors of your theme and the logo/favicon.

The ThemePageControllerExtension will make sure that:
- You include jquery (and jquery ui if needed). It's always nice to have this loaded first to avoid any further inclusion which
could break plugins (typically if loading the min version in place of the full version).
- It will compile the styles if needed. Please note that the styles are only
automatically refreshed in dev mode.

Two conventions are used by the Theme Framework:
- Your theme MUST include a "all.less" file in the css folder. This all.less must be compiled as a all.css file that
will be used by default if no compiled style is found.
- Your theme SHOULD include a "init.js" file in the javascript folder.

All compiled styles are stored in assets/Theme folder to avoid versioning compiled versions of your theme.

Ui Kit
-------------

For webapps, I recommend using the Ui Kit framework to get most of what you need
http://getuikit.com/

To enable, simply use the following yml config:

	ThemePageControllerExtension:
	  uikit:
		enabled: true
        theme_enabled: false
		theme: 'almost-flat'

You can choose to use a already compiled theme or use the less files. It is useful to
use the less files if you want to overwrite variables based on your own color scheme.

NOTE: don't forget that less variables are lazy evaluated, meaning that you can define
variables AFTER importing the uikit.less

NOTE: if you choose to import less files, you can import any of the base themes.
For the icons, to avoid messing with paths, it's easier to copy the "fonts" directory
right inside your theme folder

Google Fonts
-------------

By default the theme framework will load Open Sans as the default font.
You can define the fonts of your choice or disabling this setting

	SiteConfig:
	  google_fonts: 'Open+Sans:400italic,400,600&subset=latin,latin-ext'

Noty
-------------

The theme framework comes with a notification system that use Noty
https://github.com/needim/noty

Messages can also be pulled from the session by using SetSessionMessage method on the controller

Outdated browsers
-------------

This plugin is integrated and enabled by default with IE9 as a minimum
https://github.com/burocratik/outdated-browser

Icomoon
-------------

This module comes bundled with a all Fontawesome. Feel free
to include your own set of icons created through the online app:
https://icomoon.io/app/ 

Default CSS for Silverstripe
-------------

Libraries: 

- Mixins
- Normalize

Please note that since less files are compiled through php, your less librairies
should not make use of Javascript functions (like Lesshat does).

Default styles:

- Layout
- Typography
- Form

The css framework comes with a series of breakpoints that should fit most projects.
You can check the "variables.less" files.

The Grid
-------------

This module includes a standalone grid system compatible with Silverstripe Forms

How to create a theme?
-------------

Create a theme as usual ! Simply include the less files you need. Typically, I'll have
the following files:

- all.less which includes all other files
- typography.less
- variables.less
- layout.less
- form.less
- fonts.less
- editor.less

Include Theme Framework base less files in each, for instance in variables.less

	@import "../../../theme-framework/css/variables.less";

Or in layout.less

	@import "variables.less";
	@import "../../../theme-framework/css/mixins.less";
	@import "../../../theme-framework/css/layout.less";

Google Analytics Helper
-------------

It is a common scenario to have Google Analytics in your project. Although modules
exists to do so in Silverstripe, I always feel it is a bit of a shame to include
a whole module just to add a snippet at the bottom of your pages.
It is also a common scenario to let your user configure their tracking code.
Therefore, this module comes with a little helper to include tracking code.

Thirdparty libs
-------------

Icon generator
https://github.com/chrisbliss18/php-ico

Less compiler
https://github.com/oyejorge/less.php

Compatibility
=============
Tested with 3.1

Maintainer
==========
LeKoala - thomas@lekoala.be
