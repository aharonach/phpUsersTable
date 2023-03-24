const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));

function buildStyles() {
    return gulp.src('./public/scss/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./public/css'));
}

exports.buildStyles = buildStyles;
exports.watch = function () {
    gulp.watch('./public/scss/**/*.scss', buildStyles);
};