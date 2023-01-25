import imagemin from 'imagemin';
import imageminPngquant from 'imagemin-pngquant';

(async () => {
	await imagemin(['images/*.png'], {
		destination: 'uploads/photos',
		plugins: [
			imageminPngquant({
				quality: [0.1,0.9]
			})
		]
	});

	console.log('Images optimized');
})();