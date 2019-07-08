var gulp = require('gulp');
var sass = require('gulp-sass');
var sassGlob = require('gulp-sass-glob');
var prefix = require('gulp-autoprefixer');
var plumber = require('gulp-plumber');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var cleanCss = require('gulp-clean-css');
var rename = require('gulp-rename');


// Convert scss to css
gulp.task('scss', function (done) {
	gulp.src('./assets/scss/app.scss')
		.pipe(plumber())
		.pipe(sassGlob())
		.pipe(sass({
			errLogToConsole: true
		}))
		.pipe(prefix(['ie >= 10', 'ff >= 30', 'chrome >= 34', 'safari >= 7', 'opera >= 23', 'ios >= 7', 'android >= 4.4']))
		.pipe(cleanCss({
			compatibility: 'ie9'
		}))
		.pipe(rename('styles.min.css'))
		.pipe(gulp.dest('./public'));

	done();
});


// Concat js
gulp.task('js', function (done) {
	gulp.src('./assets/javascript/*.js')
		.pipe(plumber())
		.pipe(uglify())
		.pipe(concat('scripts.min.js'))
		.pipe(gulp.dest('./public/'));

	done();
});


// Watch src updates
gulp.task('watch', function (done) {
	gulp.watch(['./assets/**/*'], gulp.parallel('scss', 'js'));

	done();
});


// Prepare to public
gulp.task('default', gulp.parallel('scss', 'js', 'watch'));