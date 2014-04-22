module.exports = {
	
	options: {
		mangle: true
	},

	dist: {
		files: {
			'../assets/js/app.min.js': [ '../assets/js/app.js']
		}
	}
	
};