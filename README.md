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
