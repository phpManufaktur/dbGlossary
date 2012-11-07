### dbGlossary

dbGlossary help you to create and manage a glossary and/or a literature list in a easy way for your website!

* dbGlossary serves, to simplify use and management of abbreviations, acronyms, special terms and cross-references.
* with dbGlossary you are enabled to generate glossaries (keyword lists) automatically.
* dbGlossary contains a literature management for literature references according to the rules.
* dbGlossary provides a footnote management which automatically marks literature references and lists them at the end of the page.

#### Requirements

* PHP 5.2.x or newer
* use of [WebsiteBaker] [1] _or_ [LEPTON CMS] [2]
* Add-on [dbConnect_LE] [3] installed
* Add-on [rhTools] [4] installed
* Add-on [Dwoo] [5] installed 

#### Installation

* download the actual [dbGlossary_x.xx.zip] [6] installation archive
* in CMS backend select the file from "Add-ons" -> "Modules" -> "Install module"

#### Working with keywords

Add your _keywords to the database by means of the __keyword__ dialogue.

In the __keyword__ dialogue you define the type of the keyword (abbreviation, acronym, special term or cross-reference) and also define the explanation that the user of your website will be shown.

In the <b>glossary</b> the keywords defined are listed in alphabetical order and their behaviour is according to your definition.</p>

In order to use _keywords_ within the text in your website, a _keyword_ has to be preceeded and followed by two vertical strokes, so called _pipes_:

    ||keyword example||
    
_Keywords_ marked in this way are replaced by a HTML compliant marking when the page is displayed. You can find the proposed forms of display used by _dbGlossary_ in __Settings__. You can freely define and control the __format__ in the file _screen.css_ of the template you use.

Very soon you will learn that it is very easy and comfortable to mark your text. With cross-reference you can gain a function to refer to often used links (e.g. your contact form, etc.) ...

#### Creation of keyword lists

Insert the droplet `[[glossary_list]]` at any position within your WYSIWYG page.

The droplet has several parameters which enable you to control the contents of the list. Formatting of the list is done within the file _screen.css_ of the template you use.

#### Working with footnote management

In order to include _literature references_ first enter some literature sources by means of __source__ and define __identifiers__.

Footnote management supports _free remarks_ and _literature references_.

In order to include a free remark in a text, insert

    ||{...this is a free remark}||
    
at the desired position within your text. _dbGlossary_ replaces this _free remark_ by an automatically numbered superscripted number<sup>1</sup> and creates a link for it.

To tell dbGlossary where to display the footnotes please insert

    ||{footnotes}||
    
at the desired position (e.g. at the end of the text). At this position dbGlossary will list remarks and literature references contained in the text in separate lines.

Example: To insert a literature reference for a _literature source_ with the _identifier_ "JS Missing Manual", enter:

    ||{source:JS Missing Manual}||
    
With __source:__ you name the _literature source_, which according to the rules should contain _author, title, subtitle, place of publication, edition_ and _year of publication_. Instead of __source:__ you may use the shortform __s:__.

If in addition you want to insert the page(s) you are referring to, please enter:

    ||{s:JS Missing Manual|r:pages 124-126}||
    
A pipe __|__ serves to separate the parameters. These can be entered in any sequence. With __r:__ or __remark:__ you insert an additional annotation. In our example the pages 124-126. dbGlossary completes the literature reference accordingly.

[1]: http://websitebaker2.org
[2]: http://lepton-cms.org
[3]: https://github.com/phpManufaktur/dbConnect_LE/downloads
[4]: https://github.com/phpManufaktur/rhTools/downloads
[5]: https://github.com/phpManufaktur/Dwoo/downloads
[6]: https://github.com/phpManufaktur/dbGlossary/downloads