# Dynamic Text Group

-  Version: 3.0
-  Author: [Brock Petrie](http://www.brockpetrie.com)
-  Build Date: 2012-10-28
-  Requirements: Symphony 2.3

## Description

This field provides a method to dynamically add groups of text fields and text-representable fields (including radio, checkbox and dropdown inputs) to an entry. An example of its use would be for creating tracklists in a section for record albums. This extension would allow you to create as many track entries in each album entry as needed.

## Usage

1.  Add the `dynamictextgroup` folder to your Extensions directory
2.  Enable the extension from the Extensions page

## Field Editor

The Field Editor allows for easy manipulation and customization of each field's instance. When adding a new subfield, the label MUST be unique or it'll crash and burn. This is because it names the SQL columns based on your input, and SQL column names must be unique.

## Change log

**Version 3.0 dev1**

-  Official release.

**Version 2.0 ** 

-  See [v2.0](https://github.com/brockpetrie/dynamictextgroup/tree/v2.0)

**Version 1.0** 

-  Initial release. See [v1.0](https://github.com/brockpetrie/dynamictextgroup/tree/v1.0)