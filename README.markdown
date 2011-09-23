# Dynamic Text Group

-  Version: 2.0dev6
-  Author: [Brock Petrie](http://www.brockpetrie.com)
-  Build Date: 2011-09-21
-  Requirements: Symphony 2.2

## Description

This field provides a method to dynamically add a text field or text field groups to a section entry. An example of its use would be for creating tracklists in a section for record albums. This extension would allow you to create as many track entries in each album entry as needed.

## Usage

1.  Add the `dynamictextgroup` folder to your Extensions directory
2.  Make sure Nils Hörrmann's [Stage](https://github.com/nilshoerrmann/stage) exists in dynamictextgroup/lib/stage
2.  Enable the extension from the Extensions page

## Field Editor

The Field Editor in v2 replaces the old custom schema field, and allows for easy manipulation and customization of each field's instance. When adding a new subfield, the label MUST be unique or it'll crash and burn. This is because it names the SQL columns based on your input, and SQL column names must be unique.

## To-do

-  Wrap my head around how params could work with this field
-  Add more advanced filtering options
-  Make duplicate field creation impossible.

## Change log

**Version 2.0dev6**

- `2.0dev6b`: allow for bigger schemas in the `schema` column of the extension's DB table (from varchar(255) to varchar(5000)). manually upped the column length awhile ago in my dev environment and forgot to update the extension.driver.php file.
- Added 2 new fieldtypes: Checkbox and Radio. Checkbox allows for any amount of checked boxes in the subfield's column; Radio allows for only a single checked radio button in the subfield's column.
- Fixed validation pattern presets (backslashes weren't being escaped).
- Minor bugfixes and code improvements.

**Version 2.0dev5**

- Added option to declare validation rules for textfields; added 'number', 'email' and 'URI' pattern presets.
- Added placeholder attribute to textfields.
- Minor bugfixes

**Version 2.0dev4**

- Added option to choose between 2 fieldtypes for a subfield: Textfield (default) and Select List. If Select List is chosen, an additional field is displayed that allows for entering the preset options for the select list (entered as comma-separated values; e.g. 'Choice 1, Choice 2, Choice 3, Choice 4').
- Stylistic changes

**Version 2.0dev3**

-  Added basic filtering capability. Accepted filter data is 'handle:value' (e.g. first-name:Brock), where 'handle' is the handle of one of your subfields, and 'value' is the value of said subfield.

**Version 2.0dev2** 

-  Added option to allow for specific subfields to be required (not left empty).
-  Warn and (sort of) prevent user from trying to save subfields with duplicate labels. Need to make it foolproof, but at least it throws a warning now.
-  Data is now sent and stored as JSON.

**Version 2.0dev1** 

-  Implemented the Field Editor.

**Version 1.0** 

-  Initial release.
