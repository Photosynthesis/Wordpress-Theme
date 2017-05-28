# Fellowship of Intentional Communities Wordpress Theme

This is the wordpress theme used by FIC. It uses Webpack for asset compilation
& Bootstrap4 as a SASS Framework.

Right now this is mostly a port of our old Headway theme, but it will
eventually use Elm + REST & possibly be redesigned.

```
# Install Dependencies
npm i
# Build Minimized Scripts & Styles
npm run build
# Watch Source Files & Build Scripts & Styles for Development
npm run watch
```


## Migrating

1. Add & Activate New Theme
1. Regenerate Thumbnails
1. Move old widgets to new sidebar
1. Set Default Avatar
1. Disable Formidable Styling: Admin -> Formidable -> Settings
1. Replace Directory View(List & Details)
1. Remove `filter=1` from Directory View Shortcodes
1. Remove `/favicon.ico` or make symlink to theme favicon.
1. Add `row` div to before/after fields to directory search form settings.
1. Add google custom search wrapper div to no results directory search page.
1. Change page templates to store:
  * Shipping & Returns
  * New Items
  * Cart
  * Checkout
1. Change Directory RSS Page templates.


## Code Style

* Indent with 2 spaces.
* Namespace functions using classes, prefer classes that don't require
  instantiation using static functions.
* Prefix class names with `Theme` if necessary.
* Classes with actions, filters, & shortcodes should live in the `includes/`
  subdirectory.
* Any functions used only by other methods in the class should be marked as
  private.


## License

I dunno, I tried getting this answered by the FIC Board, but they're too busy.
I'd love it to be GPLv3, but please contact the author or office@ic.org for
permission to use this code.
