const mix = require('laravel-mix');
const fs = require('fs');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */


if (process.env.section) {
  require(`${__dirname}/webpack.mix.${process.env.section}.js`);
} else {
  require(`${__dirname}/webpack.mix.main.js`);
}


