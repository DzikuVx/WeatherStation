var gulp = require('gulp');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var minifyCSS = require('gulp-minify-css');

var sources = {};

sources.css = [
    './bower_components/bootstrap/dist/css/bootstrap.min.css',
    './bower_components/bootstrap/dist/css/bootstrap-theme.min.css',
    './bower_components/bootstrap-material-design/dist/css/material-wfont.min.css',
    './bower_components/bootstrap-material-design/dist/css/ripples.min.css',
    './css/index.css'
];

sources.js = [
    './bower_components/jquery/dist/jquery.min.js',
    './bower_components/bootstrap/dist/js/bootstrap.min.js',
    './js/weather-station.js',
    './bower_components/bootstrap-material-design/dist/js/ripples.min.js',
    './bower_components/bootstrap-material-design/dist/js/material.min.js'
];

gulp.task('buildCss', function () {

    return gulp.src(sources.css)
        .pipe(concat('styles.css'))
        .pipe(gulp.dest('./build/'));
});

gulp.task('buildJs', function () {

    return gulp.src(sources.js)
        .pipe(concat('script.js'))
        .pipe(gulp.dest('./build/'));
});

gulp.task('deployCss', function () {

    return gulp.src(sources.css)
        .pipe(concat('styles.css'))
        .pipe(minifyCSS())
        .pipe(gulp.dest('./build/'));
});

gulp.task('deployJs', function () {

    return gulp.src(sources.js)
        .pipe(concat('script.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./build/'));
});

gulp.task('deploy', ['deployCss', 'deployJs']);

gulp.task('watch', function () {
    gulp.watch('js/*.js', ['buildJs']);
    gulp.watch('css/*.css', ['buildCss']);
});

gulp.task('default', ['buildJs', 'buildCss']);

