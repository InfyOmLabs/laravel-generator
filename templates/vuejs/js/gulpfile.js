var elixir = require('laravel-elixir');
elixir.config.sourcemaps = false;

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.styles([
        'vue-styles.css'
    ], 'public/app/css/vue-styles.css');

    mix.browserify('crud.js', 'public/app/js/crud.js');
    // Add all model-config.js and generate 
    // then using gulp, but, generate one for each model.
	mix.scripts(['models/model-config.js'], 'public/app/js/models/model-config.js')
       
});
