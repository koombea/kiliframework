# <a href="https://www.kiliframework.org" target="_blank"><img src="https://www.kiliframework.org/wp-content/uploads/2017/09/kili-slack-43px.png"> Kili Framework</a>

[![Build Status](https://travis-ci.org/koombea/kiliframework.svg?branch=develop)](https://travis-ci.org/koombea/kiliframework) [![Code Climate](https://codeclimate.com/github/koombea/kiliframework/badges/gpa.svg)](https://codeclimate.com/github/koombea/kiliframework) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/fcc1b5b632ff43c7b89d1383360d2483)](https://www.codacy.com/app/fabolivark/kiliframework?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=koombea/kiliframework&amp;utm_campaign=Badge_Grade)

A fresh framework for developing themes in Wordpress.

Kili is a framework for agile and modular theme development for Wordpress. It separates the views from the business logic and makes it easier to re-use code.

## Installation

Kili shares the same server requirements as a regular WordPress installation. [See requirements](https://wordpress.org/about/requirements/).

Install Kili as you would a regular WordPress theme. See the [WordPress Theme Installation Guide](http://www.wpbeginner.com/beginners-guide/how-to-install-a-wordpress-theme/).

Once installed, a couple of plugins are required:

* [Advanced Custom Fields](https://github.com/AdvancedCustomFields/acf)
* [Timber](https://github.com/timber/timber)

See the [WordPress Plugin Installation Guide](http://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/).

## Developing a Child Theme

See the [Kili Wiki](https://github.com/koombea/kiliframework/wiki) for detailed instructions.

## Contributing

See [contributing](CONTRIBUTING.md)

## Version History

* 0.0.4
  * HotFix: support for page template
* 0.0.3
  * Template type names to be used for dynamic hooks
  * Augment native template hierarchy with non-PHP template processing.
  * Optimize theme hierarchy routes for twig views
  * Child theme support
* 0.0.2
  * ACF and Timber integration into the parent theme. It now works without the need of a child theme.
  * Route updates.
* 0.0.1
  * Initial development version.

## Issues

Use the Issues tab to report bugs or to suggest new features.

## Meta

Distributed under the GNU/GPL v3 license. See [License](License.txt) for more information.
