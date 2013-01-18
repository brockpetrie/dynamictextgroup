# Dynamic Text Group

-  Version: 3.0 beta 1
-  Author: [Brock Petrie](http://www.brockpetrie.com)
-  Build Date: 2013-01-18
-  Requirements: Symphony 2.3

## Description

This field provides a method to dynamically add groups of text fields and text-representable fields (including radio, checkbox and dropdown inputs) to an entry. An example of its use would be for creating tracklists in a section for record albums. This extension would allow you to create as many track entries in each album entry as needed.

## Usage

1.  Add the `dynamictextgroup` folder to your Extensions directory
2.  Enable the extension from the Extensions page
3.  Add the `Dynamic Text Group` field to your section, then save your section to enable the Field Editor
4.  Go nuts

## Field Editor

The Field Editor allows for easy manipulation and customization of each field's instance. When adding a new subfield, the label MUST be unique or it'll crash and burn. This is because it names the SQL columns based on your input, and SQL column names must be unique.

v3.0 now uses Select2 as its select box mechanism. This means that dynamic values in your select list are now possible, though you'll have to get your hands pretty dirty by creating an instantiation function in `assets/dynamictextgroup.publish.js` (instantiating it with a unique classname), and then entering that classname (without the leading `.`) in the Custom Override field within the Select Box field's options dropdown. This will attach your unique class to the select element, allowing for your jQuery instantiation plugin to find it and do its magic. I've included an example that dynamically pulls movies from Rotten Tomatoes to give you a starting point; just enter 'rottentomatoes-example' in the Custom Override option (please don't abuse my API key, I included it as a kindness; go [register for your own](http://developer.rottentomatoes.com/) if you want to actually use it, it takes like 20 seconds). In the distant future I'd like to make implementing these custom selects more user-friendly, but the potential use cases are so varied that it's scary and daunting. Anyway, with a bit of tinkering, it shouldn't be too hard for you to set this up to pull dynamic data from your Symphony site via the [APIPage](https://github.com/iwyg/apipage) plugin.

## Change log

**Version 3.0 beta 1**

-  Symphony 2.3 compatibility.
-  Switched Select Box mechanism to use [Select2](http://ivaynberg.github.com/select2/), so it's now Select Box on steroids. Dynamic select values and more!
-  Just a heads up: I'm planning on renaming this extension to *Dynamic Field Groups* at some point in the future, so brace yourselves.

**Version 2.0** 

-  See [v2.0](https://github.com/brockpetrie/dynamictextgroup/tree/v2.0)

**Version 1.0** 

-  Initial release. See [v1.0](https://github.com/brockpetrie/dynamictextgroup/tree/v1.0)