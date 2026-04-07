const { src, dest, watch, series, parallel } = require('gulp');
const sassCompiler = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const gulpAutoprefixer = require('gulp-autoprefixer');
const rename = require('gulp-rename');

const autoprefixer = gulpAutoprefixer.default || gulpAutoprefixer;

const paths = {
    styles: {
        app: 'resources/scss/app.scss',
        bootstrap: 'resources/scss/bootstrap.scss',
        icons: 'resources/scss/icons.scss',
        all: 'resources/scss/**/*.scss',
        dest: 'public/build/css'
    },
    js: {
        src: 'resources/js/**/*',
        dest: 'public/build/js'
    },
    assets: {
        fonts: { src: 'resources/fonts/**/*', dest: 'public/build/fonts' },
        images: { src: 'resources/images/**/*', dest: 'public/build/images' },
        json: { src: 'resources/json/**/*', dest: 'public/build/json' },
        libs: { src: 'resources/libs/**/*', dest: 'public/build/libs' }
    }
};

function compileStyle(entryPath, outputName) {
    return function styleTask() {
        return src(entryPath, { allowEmpty: true })
            .pipe(sassCompiler.sync({ includePaths: ['node_modules', 'resources/scss'] }).on('error', sassCompiler.logError))
            .pipe(autoprefixer())
            .pipe(cleanCSS({ level: 2 }))
            .pipe(rename(outputName))
            .pipe(dest(paths.styles.dest));
    };
}

const stylesApp = compileStyle(paths.styles.app, 'app.min.css');
const stylesBootstrap = compileStyle(paths.styles.bootstrap, 'bootstrap.min.css');
const stylesIcons = compileStyle(paths.styles.icons, 'icons.min.css');

function scripts() {
    return src(paths.js.src, { allowEmpty: true })
        .pipe(dest(paths.js.dest));
}

function fonts() {
    return src(paths.assets.fonts.src, { allowEmpty: true })
        .pipe(dest(paths.assets.fonts.dest));
}

function images() {
    return src(paths.assets.images.src, { allowEmpty: true })
        .pipe(dest(paths.assets.images.dest));
}

function json() {
    return src(paths.assets.json.src, { allowEmpty: true })
        .pipe(dest(paths.assets.json.dest));
}

function libs() {
    return src(paths.assets.libs.src, { allowEmpty: true })
        .pipe(dest(paths.assets.libs.dest));
}

function mdiCss() {
    return src('node_modules/@mdi/font/css/materialdesignicons.min.css', { allowEmpty: true })
        .pipe(dest(paths.styles.dest));
}

function mdiFonts() {
    return src('node_modules/@mdi/font/fonts/materialdesignicons-webfont.*', { allowEmpty: true })
        .pipe(dest(paths.assets.fonts.dest));
}

const styles = parallel(stylesBootstrap, stylesIcons, stylesApp);
const assets = parallel(fonts, images, json, libs);
const mdiAssets = parallel(mdiCss, mdiFonts);
const build = series(parallel(styles, scripts, assets), mdiAssets);

function watchFiles() {
    watch(paths.styles.all, styles);
    watch(paths.js.src, scripts);
    watch(paths.assets.fonts.src, fonts);
    watch(paths.assets.images.src, images);
    watch(paths.assets.json.src, json);
    watch(paths.assets.libs.src, libs);
}

exports.styles = styles;
exports.scripts = scripts;
exports.assets = assets;
exports.mdi = mdiAssets;
exports.build = build;
exports.default = series(build, watchFiles);
