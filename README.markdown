# Dynamic Text Group

- Version: 1.0
- Author: [Brock Petrie](http://www.brockpetrie.com)
- Build Date: 2011-06-05
- Requirements: Symphony 2.2

This extension's codebase is highly based on Nils Hörrmann's [Date/Time extension](http://symphony-cms.com/discuss/thread/27336/), and as such, Nils probably deserves much more credit than I do for this extension.

## Description
This field provides a method to dynamically add a text field or text field groups to a section entry. An example of its use would be for creating tracklists in a section for record albums. This extension would allow you to create as many track entries in each album entry as needed.

## Usage
1. Add the `dynamictextgroup` folder to your Extensions directory
2. Make sure Nils Hörrmann's [Stage](https://github.com/nilshoerrmann/stage) exists in dynamictextgroup/lib/stage
2. Enable the extension from the Extensions page

## Configuration
When this field is first added to a section, you'll need to declare how many text fields you want in each group/row. For example, if you are creating a tracklist field, you'd most likely want 3 text fields (track number, track title, track length). (IMPORTANT: once you save your section blueprint, this field no longer becomes editable. This is because it creates a database table with the amount of columns specified in this input.) You'll also have the option to declare a custom schema for your group, which is explained below.

## Custom Group Schemas
By default, the extension gives each textfield in your group a generic name (e.g. 'textfield1', 'textfield2' & 'textfield3') and displays them proportionally, widthwise, in the publish section ('33.3%', '33.3%', & '33.3%'). The generic names are also the names used for that textfield's node in the XML output. The Custom Schema input allows you to declare your own template that determines how the field's entries are displayed and outputted. This is the format:

text_field1_label,text_field1_width|text_field2_label,text_field2_width|text_field3_label,text_field3_width

To better explain, I'll continue with my tracklisting example. Without declaring a custom schema, we'd just have 3 textfields with generic names and equal widths. Ideally, we'd like to tweak that and set our own field widths and field names to better fit what we're using this for. First, we can declare a label for each text field like so:
Track Number|Track Name|Track Length

Now in the Publish panel you'll see that our textfields are appropriately labeled. The XML output of our field is also updated, with each respective node now using a handle based on your label name.

Everything looks good, except for the fact that all 3 of our textfields are given equal weight, widthwise, in the Publish panel. Ideally, we would size each textfield to better fit the content it's holding. For example, our Track Number and Track Length fields can be pretty small, as they only need to hold a few characters. We could adjust their widths like so:
Track Number,10|Track Title|Track Length,20

Now in the Publish panel you'll see that our textfields have been resized, with Track Number now taking up 10% of the row's space, Track Length taking up 20% of the row's space, and Track Title taking up the difference. Notice how we didn't declare a width for Track Title? The extension automatically calculates the remaining width and divides that equally amongst the fields that have no width declared.

To sum things up: the pipe character (|) separates your textfield indexes, while commas separate your textfield labels from their widths. It is possible to leave certain parts null (i.e. "Track Number|,60|Track Length,30").

## Roadmap
- Store each textfield index/column in its own table, which would allow for adding/removing columns from a dynamictextgroup instance.
- Implement a schema editor to make building schemas easier and more natural. Ideally, it will be a visual tool where you can add, rename, resize and reorder fields by simple drag/drop mechanisms.

## Change log

**Version 1.0**

- Initial release.