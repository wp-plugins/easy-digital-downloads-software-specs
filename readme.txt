﻿=== Easy Digital Downloads - Software Specs ===
Author URI: http://isabelcastillo.com
Plugin URI: http://isabelcastillo.com/easy-digital-downloads-software-specs/
Contributors: isabel104
Tags: software, application, SoftwareApplication, specs, microdata, schema, schema.org, easy digital downloads, web application
Requires at least: 3.3
Tested up to: 3.5.1
Stable Tag: 0.1
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add software specs and Software Application microdata to your downloads when using Easy Digital Downloads plugin.

== Description ==

This is an extension for [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/) that automatically does several things: 

1. It adds a Specs table below your single download content. The Specs table displays these: 

- Release date
- Last updated date
- Current version
- Software application type
- File format
- File size
- Requirements
- Price
- Currency code


2. It replaces EDD's default microdata itemptype `Product` with `SoftwareApplication`.

3. It moves the microdata itemtype declaration up to the body element so as to nest the `name` property within the itemscope.

4. It adds `offers`, `price`, and `currency` microdata in order to generate Google rich snippets for Software Applications.

5. In addition, it adds these microdata properties of `SoftwareApplication`:

* `description`
* `softwareapplicationcategory`
* `datepublished`
* `datemodified`
* `softwareversion`
* `applicationcategory`
* `fileformat`
* `filesize`
* `requirements`

6. If you DON'T have [EDD Versions plugin](http://wordpress.org/extend/plugins/edd-versions/) plugin active, then EDD Software Specs will add a "Current Version" column in the table that is outputted by the `download_history` shortcode.

= Compatible with EDD Versions plugin  =
If you do have EDD Versions active, the version meta field from that plugin will take precedence. Nothing extra is added to the `download_history` table.

For more info, go to [Easy Digital Downloads - Software Specs](http://isabelcastillo.com/easy-digital-downloads-software-specs/)

== Installation ==
**Easy Installation**

1. In your WordPress dashboard, go to `Plugins > Add New`
2. Search for "Easy Digital Downloads Software Specs"
3. When you find it, install it.

**Manual Installation via WP**

1.  After you download the plugin to your computer, login to your WordPress dashboard.
2.  Go to `Plugins > Add New`
2.  Click Upload.
3.  Browse for the "easy-digital-downloads-software-specs.zip" file that you downloaded from this page.
4.  Click "Install Now".
5.  Click "Activate Plugin"
6.  You're done.

**Really Manual Installation**

1. Unzip `easy-digital-downloads-software-specs.zip` directly into the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
== Frequently Asked Questions ==

= Why am I not getting rich snippets in Google's Structured Data Testing Tool? =

You have to select a Software Application Type for the download. "OtherApplication" doesn't qualify for rich snippets, unless, outside of this plugin, you've added either "aggregateRating" or "operatingSystems" for the particular download. Go to the download's Specs Metabox to select the Software Application Type.
== Screenshots ==

1. Front-end: Specs table as shown on single download page
2. Back-end: Specs meta box on single download editor
== Changelog ==

= 0.2: April 9, 2013 =

* Added compatibility with EDD Versions plugin.

= 0.1: April 9, 2013 =

* Initial release.