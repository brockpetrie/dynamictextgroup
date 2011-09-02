# Dynamic Text Group

- Version: 2.0dev
- Author: [Brock Petrie](http://www.brockpetrie.com)
- Build Date: 2011-09-02
- Requirements: Symphony 2.2

## Description
This field provides a method to dynamically add a text field or text field groups to a section entry. An example of its use would be for creating tracklists in a section for record albums. This extension would allow you to create as many track entries in each album entry as needed.

## Usage
1. Add the `dynamictextgroup` folder to your Extensions directory
2. Make sure Nils Hörrmann's [Stage](https://github.com/nilshoerrmann/stage) exists in dynamictextgroup/lib/stage
2. Enable the extension from the Extensions page

## Field Editor
The Field Editor replaces the old custom schema field, and allows for easy manipulation and customization of each field's instance. When adding a new subfield, the label MUST be unique or it'll crash and burn. This is because it names the SQL columns based on your input, and SQL column names must be unique.

## Roadmap
- Get filtering working

## Change log

**Version 2.0dev**
- Implemented the Field Editor, allowing easy manipulation and customization of each field's instance

**Version 1.0**
- Initial release.
