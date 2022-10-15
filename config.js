module.exports = {
	config: {
		tailwindjs: "./tailwind.config.js",
		port: 9050
	},
	paths: {
		root: "./",
		src: {
			base: "./views",
			css: "./views/css",
			js: "./views/js",
			img: "./views/img"
		},
		dist: {
			base: "./public/dist/assets",
			css: "./public/dist/assets/css",
			js: "./public/dist/assets/js",
			img: "./public/dist/assets/img"
		},
		build: {
			base: "./public/assets",
			css: "./public/assets/css",
			js: "./public/assets/js",
			img: "./public/assets/img"
		}
	}
}