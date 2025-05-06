**FAQ mod**, www.missallsunday.com

This software is licensed under [MPL 2.0 license](https://www.mozilla.org/en-US/MPL/2.0/).

######What is a FAQ mod:

Is a modification for SMF which allows you to create a simple FAQ (Frequently Asked Questions) page

######Requirements:

- SMF 2.1.x or greater.
- PHP 8.0 or greater.

######Features:

- No file edits, works on all themes
- Categories, you can add as many categories as you want.
- Permission to View/Add/Delete FAQs and categories.
- Pagination, to easily navigate through the FAQs if you have too many of them.
- 3 ways to sort your FAQs, by title, by ID or by category.
- Select the position for the FAQ button within your Main Menu.
- You can use JavaScript to hide the body/answer to save some space, to show the body/answer, just click on the title.
- You can use BBC code on your FAQs, this mod uses the SMF editor which make it easy to add/edit your FAQs.
- Custom message to show at the top of your FAQ page

######Change log

- 2.0
  - Restructured code
  - Compatibility with SMF 2.1.x
  - Simplify permissions (merging add and edit)
  - Add custom message on top
  - Sanitize all inputs, adds validation to match against predefined types for each received inputs

-1.2
  - Fixed a bug with the categories been reset when editing a faq.
  - Re-write in OOP, the mod now performs less queries by using the cache system if available.
Some code improved.

-1.1
  - Added categories
  - Permission to Add/Edit/Delete FAQs and Categories.
  - Lots of bugs fixed.
  - Previews.

-1.0.1
  - Revamp of language strings.
  - Use of javascript:void(0) instead of # on Faq.template.php.
  - Fixed an issue with the WYSIWYG editor, thanks to Tenma.

-1.0
  - Finished first version.