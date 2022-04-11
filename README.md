# TYPO3 Extension mask_export

[![Latest Stable Version](https://img.shields.io/packagist/v/ichhabrecht/mask-export.svg)](https://packagist.org/packages/ichhabrecht/mask-export)
[![Build Status](https://img.shields.io/travis/IchHabRecht/mask_export/main.svg)](https://travis-ci.org/IchHabRecht/mask_export)
[![StyleCI](https://styleci.io/repos/63010277/shield?branch=main)](https://styleci.io/repos/63010277)

Want to create new content elements the easy way?

Use [mask](http://mask.webprofil.at) and its drag & drop wizard to create content elements the easy way.

Use mask_export to export the content elements into an own extension.

## Why

A content element needs some TCA information, TypoScript and database configuration and template files.
Actually there is no need to depend on any third party extension when dealing with content elements.

But you don't want to deal with different information in different files and folders.
You want to be able to concentrate on what is necessary for the user, not how it has to be implemented in your TYPO3 CMS.

By using mask and mask_export you can do exactly this! Simply create your own content elements by using a drag & drop wizard.
Add fields, repeating items, nested content elements within minutes.

## Why not

Mask offers an easy way to create content elements but has some disadvantage when you need to take care about performance.
Especially the frontend rendering can take at lot of time for uncached pages.
Instead of relying on TYPO3 CMS core rendering, all elements are rendered by an own Extbase Controller.
As this was needed to support former version of TYPO3 CMS, it was deprecated with the introduction of fluid_styled_content and the concepts of DataProcessors in TYPO 7 LTS.

This is what mask_export is developed for. It takes the content element information from the mask configuration and generates the needed
code to get those elements to work with pure TYPO3 CMS core functionality out of the box.
It bundles all necessary information into an own extension that can be installed and used in every other TYPO3 CMS system.

## Installation

Simply install mask and mask_export with Composer or the Extension Manager.

`composer require ichhabrecht/mask-export`

## Usage

- use the mask wizard to configure own content elements
- change to tab "Code Export"
- if you like change the extension key, the default one is *my_mask_export*
- either install or download your extension

## Best practise

It is recommended to **not touch** the generated export extension.
Instead you should add necessary changes and your own templates into a [site package](https://sitepackagebuilder.com/).

This ensures you can edit your content elements within the mask wizard (add further content elements, change settings)
and still be able to use the new extension version in your existing system.

You can find some common configuration in the [my_maskexport_sitepackage](https://github.com/IchHabRecht/my_maskexport_sitepackage)
example site package.

Furthermore you can refer to the slides [CCE (Custom Content Elements) - Best Practice ](https://de.slideshare.net/cpsitgmbh/cce-custom-content-elements-best-practice)
for additional information. 

## Community

- Thanks to [Marcus Schwemer](https://twitter.com/MarcusSchwemer) who wrote about mask_export in his blog [TYPO3worx](https://typo3worx.eu/2018/03/eight-typo3-extensions-making-developers-happy/)
- Thanks to [Thomas Löffler](https://spooner-web.de) for his ongoing support as [Patron](https://www.patreon.com/IchHabRecht)
