const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');


mix.react('resources/js/client.js', 'public/client')
.sass('resources/sass/client/client.scss','public/client')
.mergeManifest();
