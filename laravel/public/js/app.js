/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/css/app.css":
/*!*******************************!*\
  !*** ./resources/css/app.css ***!
  \*******************************/
/***/ (() => {

throw new Error("Module build failed (from ./node_modules/mini-css-extract-plugin/dist/loader.js):\nModuleBuildError: Module build failed (from ./node_modules/postcss-loader/dist/cjs.js):\nError: Failed to find 'tailwindcss'\n  in [\n    C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\resources\\css\n  ]\n    at C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\postcss-import\\lib\\resolve-id.js:35:13\n    at async LazyResult.runAsync (C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\postcss\\lib\\lazy-result.js:293:11)\n    at async Object.loader (C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\postcss-loader\\dist\\index.js:97:14)\n    at processResult (C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\webpack\\lib\\NormalModule.js:891:19)\n    at C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\webpack\\lib\\NormalModule.js:1037:5\n    at C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\loader-runner\\lib\\LoaderRunner.js:400:11\n    at C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\loader-runner\\lib\\LoaderRunner.js:252:18\n    at context.callback (C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\loader-runner\\lib\\LoaderRunner.js:124:13)\n    at Object.loader (C:\\Users\\Eric Ciclo\\Desktop\\DAW\\DAW TARDA\\M12\\Valth\\laravel\\node_modules\\postcss-loader\\dist\\index.js:142:7)");

/***/ }),

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");


/***/ }),

/***/ "./resources/js/bootstrap.js":
/*!***********************************!*\
  !*** ./resources/js/bootstrap.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
Object(function webpackMissingModule() { var e = new Error("Cannot find module 'axios'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());

window.axios = Object(function webpackMissingModule() { var e = new Error("Cannot find module 'axios'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	__webpack_require__("./resources/js/app.js");
/******/ 	// This entry module doesn't tell about it's top-level declarations so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__("./resources/css/app.css");
/******/ 	
/******/ })()
;