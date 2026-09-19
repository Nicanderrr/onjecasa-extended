# Hostinger `public_html` files

Copy the contents of this folder into Hostinger's `public_html` directory.

Keep the Laravel application beside it:

```text
/home/ACCOUNT/
├── onje-casa/
│   ├── app/
│   ├── bootstrap/
│   ├── storage/
│   ├── vendor/
│   └── .env
└── public_html/
    ├── .htaccess
    └── index.php
```

The included `index.php` expects the application directory to be named `onje-casa`. If you use another name, edit `$laravelRoot` in `index.php` before uploading.

Also copy the contents of the project's `public/` directory into `public_html` so CSS, JavaScript, images, uploads, and `build/manifest.json` remain available.
