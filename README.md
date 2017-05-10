# Fellowship of Intentional Communities Wordpress Theme

This is the wordpress theme used by FIC. It uses Webpack for asset compilation
& Bootstrap4 as a SASS Framework.

Right now this is mostly a port of our old Headway theme, but it will
eventually use Elm + REST & possibly be redesigned.

```
npm i
npm run build
npm run watch
```

## TODO

* Fix nav clicking on chrome, also nav get stuck open on click
* Navbar wrap - maybe reduce custom search wide on smaller screens
* Finish Navs
* Cleanup Wiki Tabs
* Classifieds
  * Place/Preview/Edit Ad Form
  * Locations
  * Manage Form
* Directory
* WooCommerce
* Mobile/Tablet Responsiveness
* Style/Design/Font/Color Tweaks
* Structured SEO
* Migrate functions.php from old theme


## Migrating

1. Add & Activate New Theme
1. Replace Directory View(List & Details)
1. Remove `filter=1` from Directory View Shortcodes
1. Disable Formidable Styling: Admin -> Formidable -> Settings
1. Regenerate Thumbnails
1. Set Default Avatar


## License

I dunno, I tried getting this answered by the FIC Board, but they're too busy.
I'd love it to be GPLv3, but please contact the author or office@ic.org for
permission to use this code.
