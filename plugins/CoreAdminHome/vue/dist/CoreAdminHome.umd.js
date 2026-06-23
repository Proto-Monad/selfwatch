(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["CoreAdminHome"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["CoreAdminHome"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__, __WEBPACK_EXTERNAL_MODULE_a5a2__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "plugins/CoreAdminHome/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "a5a2":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_a5a2__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "ArchivingSettings", function() { return /* reexport */ ArchivingSettings; });
__webpack_require__.d(__webpack_exports__, "BrandingSettings", function() { return /* reexport */ BrandingSettings; });
__webpack_require__.d(__webpack_exports__, "SmtpSettings", function() { return /* reexport */ SmtpSettings; });
__webpack_require__.d(__webpack_exports__, "TrackingFailures", function() { return /* reexport */ TrackingFailures; });

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=554c5c7e

const _hoisted_1 = {
  class: "form-group row"
};
const _hoisted_2 = {
  class: "col s12"
};
const _hoisted_3 = {
  class: "col s12 m6"
};
const _hoisted_4 = {
  class: "form-description",
  style: {
    "margin-left": "4px"
  }
};
const _hoisted_5 = {
  for: "enableBrowserTriggerArchiving2"
};
const _hoisted_6 = ["innerHTML"];
const _hoisted_7 = {
  class: "col s12 m6"
};
const _hoisted_8 = ["innerHTML"];
const _hoisted_9 = {
  class: "form-group row"
};
const _hoisted_10 = {
  class: "col s12"
};
const _hoisted_11 = {
  class: "input-field col s12 m6"
};
const _hoisted_12 = ["disabled"];
const _hoisted_13 = {
  class: "form-description"
};
const _hoisted_14 = {
  class: "col s12 m6"
};
const _hoisted_15 = {
  key: 0,
  class: "form-help"
};
const _hoisted_16 = {
  key: 0
};
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_ArchivingSettings'),
    anchor: "archivingSettings",
    class: "matomo-archiving-settings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_AllowPiwikArchivingToTriggerBrowser')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "enableBrowserTriggerArchiving1",
      name: "enableBrowserTriggerArchiving",
      value: "1",
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.enableBrowserTriggerArchivingValue = $event)
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.enableBrowserTriggerArchivingValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Default')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "enableBrowserTriggerArchiving2",
      name: "enableBrowserTriggerArchiving",
      value: "0",
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.enableBrowserTriggerArchivingValue = $event)
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.enableBrowserTriggerArchivingValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description",
      innerHTML: _ctx.$sanitize(_ctx.archivingTriggerDesc),
      style: {
        "margin-left": "4px"
      }
    }, null, 8, _hoisted_6)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "form-help",
      innerHTML: _ctx.$sanitize(_ctx.archivingInlineHelp)
    }, null, 8, _hoisted_8)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ReportsContainingTodayWillBeProcessedAtMostEvery')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "text",
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.todayArchiveTimeToLiveValue = $event),
      id: "todayArchiveTimeToLive",
      disabled: !_ctx.isGeneralSettingsAdminEnabled
    }, null, 8, _hoisted_12), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.todayArchiveTimeToLiveValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RearchiveTimeIntervalOnlyForTodayReports')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [_ctx.isGeneralSettingsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [_ctx.showWarningCron ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("strong", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NewReportsWillBeProcessedByCron')), 1), _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ReportsWillBeProcessedAtMostEveryHour')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_IfArchivingIsFastYouCanSetupCronRunMoreOften')), 1), _hoisted_18])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_SmallTrafficYouCanLeaveDefault', _ctx.todayArchiveTimeToLiveDefault)) + " ", 1), _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MediumToHighTrafficItIsRecommendedTo', 1800, 3600)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      saving: _ctx.isLoading,
      onConfirm: _cache[3] || (_cache[3] = $event => _ctx.save())
    }, null, 8, ["saving"])])])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=554c5c7e

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var ArchivingSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    enableBrowserTriggerArchiving: Boolean,
    showSegmentArchiveTriggerInfo: Boolean,
    isGeneralSettingsAdminEnabled: Boolean,
    showWarningCron: Boolean,
    todayArchiveTimeToLive: Number,
    todayArchiveTimeToLiveDefault: Number
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data() {
    return {
      isLoading: false,
      enableBrowserTriggerArchivingValue: this.enableBrowserTriggerArchiving ? 1 : 0,
      todayArchiveTimeToLiveValue: this.todayArchiveTimeToLive
    };
  },
  watch: {
    enableBrowserTriggerArchiving(newValue) {
      this.enableBrowserTriggerArchivingValue = newValue ? 1 : 0;
    },
    todayArchiveTimeToLive(newValue) {
      this.todayArchiveTimeToLiveValue = newValue;
    }
  },
  computed: {
    archivingTriggerDesc() {
      let result = '';
      result += Object(external_CoreHome_["translate"])('General_ArchivingTriggerDescription', Object(external_CoreHome_["externalLink"])('https://matomo.org/docs/setup-auto-archiving/'), '</a>');
      if (this.showSegmentArchiveTriggerInfo) {
        result += Object(external_CoreHome_["translate"])('General_ArchivingTriggerSegment');
      }
      return result;
    },
    archivingInlineHelp() {
      let result = Object(external_CoreHome_["translate"])('General_ArchivingInlineHelp');
      result += '<br/>';
      result += Object(external_CoreHome_["translate"])('General_SeeTheOfficialDocumentationForMoreInformation', Object(external_CoreHome_["externalLink"])('https://matomo.org/docs/setup-auto-archiving/'), '</a>');
      return result;
    }
  },
  methods: {
    save() {
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'CoreAdminHome.setArchiveSettings'
      }, {
        enableBrowserTriggerArchiving: this.enableBrowserTriggerArchivingValue,
        todayArchiveTimeToLive: this.todayArchiveTimeToLiveValue
      }).then(() => {
        this.isLoading = false;
        const notificationId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationId);
      }).finally(() => {
        this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue



ArchivingSettingsvue_type_script_lang_ts.render = render

/* harmony default export */ var ArchivingSettings = (ArchivingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=template&id=68734f11

const BrandingSettingsvue_type_template_id_68734f11_hoisted_1 = {
  id: "logoSettings"
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_2 = {
  id: "logoUploadForm",
  ref: "logoUploadForm",
  method: "post",
  enctype: "multipart/form-data",
  action: "index.php?module=CoreAdminHome&format=json&action=uploadCustomLogo"
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_3 = {
  key: 0
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_4 = ["value"];
const BrandingSettingsvue_type_template_id_68734f11_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "force_api_session",
  value: "1"
}, null, -1);
const BrandingSettingsvue_type_template_id_68734f11_hoisted_6 = {
  key: 0
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_7 = {
  key: 0,
  class: "alert alert-warning uploaderror"
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_8 = {
  class: "row"
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_9 = {
  class: "col s12"
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_10 = ["src"];
const BrandingSettingsvue_type_template_id_68734f11_hoisted_11 = {
  class: "row"
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_12 = {
  class: "col s12"
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_13 = ["src"];
const BrandingSettingsvue_type_template_id_68734f11_hoisted_14 = {
  key: 1
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_15 = ["innerHTML"];
const BrandingSettingsvue_type_template_id_68734f11_hoisted_16 = {
  key: 1
};
const BrandingSettingsvue_type_template_id_68734f11_hoisted_17 = {
  class: "alert alert-warning"
};
function BrandingSettingsvue_type_template_id_68734f11_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_BrandingSettings'),
    anchor: "brandingSettings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_CustomLogoHelpText')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      name: "useCustomLogo",
      uicontrol: "checkbox",
      "model-value": _ctx.enabled,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.onUseCustomLogoChange($event)),
      title: _ctx.translate('CoreAdminHome_UseCustomLogo'),
      "inline-help": _ctx.help
    }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", BrandingSettingsvue_type_template_id_68734f11_hoisted_2, [_ctx.fileUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      name: "token_auth",
      value: _ctx.tokenAuth
    }, null, 8, BrandingSettingsvue_type_template_id_68734f11_hoisted_4), BrandingSettingsvue_type_template_id_68734f11_hoisted_5, _ctx.logosWriteable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
      name: "fade-out"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.showUploadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_LogoUploadFailed')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 1
    }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "file",
      name: "customLogo",
      "model-value": _ctx.customLogo,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.onCustomLogoChange($event)),
      title: _ctx.translate('CoreAdminHome_LogoUpload'),
      "inline-help": _ctx.translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '110')
    }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: _ctx.pathUserLogoSrc,
      id: "currentLogo",
      style: {
        "max-height": "150px"
      },
      ref: "currentLogo"
    }, null, 8, BrandingSettingsvue_type_template_id_68734f11_hoisted_10)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "file",
      name: "customFavicon",
      "model-value": _ctx.customFavicon,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.onFaviconChange($event)),
      title: _ctx.translate('CoreAdminHome_FaviconUpload'),
      "inline-help": _ctx.translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '16')
    }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: _ctx.pathUserFaviconSrc,
      id: "currentFavicon",
      width: "16",
      height: "16",
      ref: "currentFavicon"
    }, null, 8, BrandingSettingsvue_type_template_id_68734f11_hoisted_13)])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.logosWriteable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "alert alert-warning",
      innerHTML: _ctx.$sanitize(_ctx.logosNotWriteableWarning)
    }, null, 8, BrandingSettingsvue_type_template_id_68734f11_hoisted_15)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.fileUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_68734f11_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_FileUploadDisabled', "file_uploads=1")), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      onConfirm: _cache[3] || (_cache[3] = $event => _ctx.save()),
      saving: _ctx.isLoading
    }, null, 8, ["saving"])])), [[_directive_form]])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=template&id=68734f11

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=script&lang=ts



const {
  $
} = window;
/* harmony default export */ var BrandingSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    fileUploadEnabled: {
      type: Boolean,
      required: true
    },
    logosWriteable: {
      type: Boolean,
      required: true
    },
    useCustomLogo: {
      type: Boolean,
      required: true
    },
    pathUserLogoDirectory: {
      type: String,
      required: true
    },
    pathUserLogo: {
      type: String,
      required: true
    },
    pathUserLogoSmall: {
      type: String,
      required: true
    },
    pathUserLogoSvg: {
      type: String,
      required: true
    },
    hasUserLogo: {
      type: Boolean,
      required: true
    },
    pathUserFavicon: {
      type: String,
      required: true
    },
    hasUserFavicon: {
      type: Boolean,
      required: true
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
    return {
      isLoading: false,
      enabled: this.useCustomLogo,
      customLogo: this.pathUserLogo,
      customFavicon: this.pathUserFavicon,
      showUploadError: false,
      currentLogoSrcExists: this.hasUserLogo,
      currentFaviconSrcExists: this.hasUserFavicon,
      newLogoBase64Src: '',
      newFaviconBase64Src: ''
    };
  },
  computed: {
    tokenAuth() {
      return external_CoreHome_["Matomo"].token_auth;
    },
    logosNotWriteableWarning() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_LogoNotWriteableInstruction', `<code>${this.pathUserLogoDirectory}</code><br/>`, `${this.pathUserLogo}, ${this.pathUserLogoSmall}, ${this.pathUserLogoSvg}`);
    },
    help() {
      if (!this.isPluginsAdminEnabled) {
        return undefined;
      }
      const giveUsFeedbackText = `"${Object(external_CoreHome_["translate"])('General_GiveUsYourFeedback')}"`;
      const linkStart = '<a href="?module=CorePluginsAdmin&action=plugins" ' + 'rel="noreferrer noopener" target="_blank">';
      return Object(external_CoreHome_["translate"])('CoreAdminHome_CustomLogoFeedbackInfo', giveUsFeedbackText, linkStart, '</a>');
    },
    pathUserLogoSrc() {
      if (this.newLogoBase64Src) {
        return `data:image/png;base64, ${this.newLogoBase64Src}`;
      }
      if (this.currentLogoSrcExists && this.pathUserLogo) {
        return this.pathUserLogo;
      }
      return '';
    },
    pathUserFaviconSrc() {
      if (this.newFaviconBase64Src) {
        return `data:image/png;base64, ${this.newFaviconBase64Src}`;
      }
      if (this.currentFaviconSrcExists && this.pathUserFavicon) {
        return this.pathUserFavicon;
      }
      return '';
    }
  },
  methods: {
    onUseCustomLogoChange(newValue) {
      this.enabled = newValue;
    },
    onCustomLogoChange(newValue) {
      this.customLogo = newValue;
      this.updateLogo();
    },
    onFaviconChange(newValue) {
      this.customFavicon = newValue;
      this.updateLogo();
    },
    save() {
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'CoreAdminHome.setBrandingSettings'
      }, {
        useCustomLogo: this.enabled ? '1' : '0',
        hasCustomLogo: this.newLogoBase64Src.length > 0 || this.customLogo ? '1' : '0',
        hasCustomFavicon: this.newFaviconBase64Src.length > 0 || this.customFavicon ? '1' : '0'
      }).then(response => {
        this.enabled = !!response.useCustomLogo;
        if (response.customLogoPath) {
          this.customLogo = response.customLogoPath;
        }
        if (response.customFaviconPath) {
          this.customFavicon = response.customFaviconPath;
        }
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        if (!this.enabled) {
          // reset fields so that values are not visible when setting is enabled without page reload
          this.currentLogoSrcExists = false;
          this.currentFaviconSrcExists = false;
          this.customLogo = '';
          this.customFavicon = '';
          this.newFaviconBase64Src = '';
          this.newLogoBase64Src = '';
        }
        this.isLoading = false;
      });
    },
    updateLogo() {
      const isSubmittingLogo = !!this.customLogo;
      const isSubmittingFavicon = !!this.customFavicon;
      if (!isSubmittingLogo && !isSubmittingFavicon) {
        return;
      }
      this.showUploadError = false;
      const frameName = `upload${new Date().getTime()}`;
      const uploadFrame = $(`<iframe name="${frameName}" />`);
      uploadFrame.css('display', 'none');
      uploadFrame.on('load', () => {
        setTimeout(() => {
          let frameContent = '';
          let frameContentJSON = {};
          try {
            frameContent = ($(uploadFrame.contents()).find('body').text() || '').trim();
            frameContentJSON = JSON.parse(frameContent);
          } catch (e) {
            // invalid JSON
          }
          if (frameContent && Object.keys(frameContentJSON).length === 0) {
            this.showUploadError = true;
          } else {
            // Upload succeed, so we update the images availability
            // according to what have been uploaded
            if (isSubmittingLogo && frameContentJSON.logo) {
              this.newLogoBase64Src = frameContentJSON.logo;
            }
            if (isSubmittingFavicon && frameContentJSON.favicon) {
              this.newFaviconBase64Src = frameContentJSON.favicon;
            }
          }
          if (frameContent) {
            uploadFrame.remove();
          }
        }, 1000);
      });
      $('body:first').append(uploadFrame);
      const submittingForm = $(this.$refs.logoUploadForm);
      submittingForm.attr('target', frameName);
      submittingForm.submit();
      this.customLogo = '';
      this.customFavicon = '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue



BrandingSettingsvue_type_script_lang_ts.render = BrandingSettingsvue_type_template_id_68734f11_render

/* harmony default export */ var BrandingSettings = (BrandingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=template&id=3c763ca6

const SmtpSettingsvue_type_template_id_3c763ca6_hoisted_1 = {
  id: "smtpSettings"
};
function SmtpSettingsvue_type_template_id_3c763ca6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_auto_clear_password = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("auto-clear-password");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_EmailServerSettings'),
    anchor: "mailSettings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "mailUseSmtp",
      modelValue: _ctx.enabled,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.enabled = $event),
      title: _ctx.translate('General_UseSMTPServerForEmail'),
      "inline-help": _ctx.translate('General_SelectYesIfYouWantToSendEmailsViaServer')
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SmtpSettingsvue_type_template_id_3c763ca6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailHost",
      "model-value": _ctx.mailHost,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.onUpdateMailHost($event)),
      title: _ctx.translate('General_SmtpServerAddress')
    }, null, 8, ["model-value", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailPort",
      modelValue: _ctx.mailPort,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.mailPort = $event),
      title: _ctx.translate('General_SmtpPort'),
      "inline-help": _ctx.translate('General_OptionalSmtpPort')
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "mailType",
      modelValue: _ctx.mailType,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.mailType = $event),
      title: _ctx.translate('General_AuthenticationMethodSmtp'),
      options: _ctx.mailTypes,
      "inline-help": _ctx.translate('General_OnlyUsedIfUserPwdIsSet')
    }, null, 8, ["modelValue", "title", "options", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailUsername",
      modelValue: _ctx.mailUsername,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.mailUsername = $event),
      title: _ctx.translate('General_SmtpUsername'),
      "inline-help": _ctx.translate('General_OnlyEnterIfRequired'),
      autocomplete: 'off'
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "password",
      name: "mailPassword",
      "model-value": _ctx.mailPassword,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.onMailPasswordChange($event)),
      onClick: _cache[6] || (_cache[6] = $event => {
        !_ctx.passwordChanged && $event.target.select();
      }),
      title: _ctx.translate('General_SmtpPassword'),
      "inline-help": _ctx.passwordHelp,
      autocomplete: 'off'
    }, null, 8, ["model-value", "title", "inline-help"]), [[_directive_auto_clear_password]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailFromAddress",
      modelValue: _ctx.mailFromAddress,
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.mailFromAddress = $event),
      title: _ctx.translate('General_SmtpFromAddress'),
      "inline-help": _ctx.translate('General_SmtpFromEmailHelp', _ctx.mailHost),
      autocomplete: 'off'
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailFromName",
      modelValue: _ctx.mailFromName,
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.mailFromName = $event),
      title: _ctx.translate('General_SmtpFromName'),
      "inline-help": _ctx.translate('General_NameShownInTheSenderColumn'),
      autocomplete: 'off'
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "mailEncryption",
      modelValue: _ctx.mailEncryption,
      "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => _ctx.mailEncryption = $event),
      title: _ctx.translate('General_SmtpEncryption'),
      options: _ctx.mailEncryptions,
      "inline-help": _ctx.translate('General_EncryptedSmtpTransport')
    }, null, 8, ["modelValue", "title", "options", "inline-help"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      onConfirm: _cache[10] || (_cache[10] = $event => _ctx.showPasswordConfirmation = true),
      saving: _ctx.isLoading
    }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.showPasswordConfirmation,
      "onUpdate:modelValue": _cache[11] || (_cache[11] = $event => _ctx.showPasswordConfirmation = $event),
      onConfirmed: _cache[12] || (_cache[12] = $event => _ctx.save($event))
    }, null, 8, ["modelValue"])])), [[_directive_form]])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=template&id=3c763ca6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var SmtpSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    mail: {
      type: Object,
      required: true
    },
    mailTypes: {
      type: Object,
      required: true
    },
    mailEncryptions: {
      type: Object,
      required: true
    }
  },
  data() {
    const mail = this.mail;
    return {
      isLoading: false,
      showPasswordConfirmation: false,
      enabled: mail.transport === 'smtp',
      mailHost: mail.host,
      passwordChanged: false,
      mailPort: mail.port,
      mailType: mail.type,
      mailUsername: mail.username,
      mailPassword: mail.password ? '******' : '',
      mailFromAddress: mail.noreply_email_address,
      mailFromName: mail.noreply_email_name,
      mailEncryption: mail.encryption
    };
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"],
    AutoClearPassword: external_CoreHome_["AutoClearPassword"]
  },
  computed: {
    passwordHelp() {
      const part1 = `${Object(external_CoreHome_["translate"])('General_OnlyEnterIfRequiredPassword')}<br/>`;
      const part2 = `${Object(external_CoreHome_["translate"])('General_WarningPasswordStored', '<strong>', '</strong>')}<br/>`;
      return `${part1}\n${part2}`;
    }
  },
  methods: {
    onUpdateMailHost(newValue) {
      this.mailHost = newValue;
      if (this.passwordChanged) {
        return;
      }
      this.mailPassword = '';
      this.passwordChanged = true;
    },
    onMailPasswordChange(newValue) {
      this.mailPassword = newValue;
      this.passwordChanged = true;
    },
    save(password) {
      this.isLoading = true;
      const mailSettings = {
        mailUseSmtp: this.enabled ? '1' : '0',
        mailPort: this.mailPort,
        mailHost: this.mailHost,
        mailType: this.mailType,
        mailUsername: this.mailUsername,
        mailFromAddress: this.mailFromAddress,
        mailFromName: this.mailFromName,
        mailEncryption: this.mailEncryption,
        passwordConfirmation: password
      };
      if (this.passwordChanged) {
        mailSettings.mailPassword = this.mailPassword;
      }
      external_CoreHome_["AjaxHelper"].post({
        module: 'CoreAdminHome',
        action: 'setMailSettings'
      }, mailSettings, {
        withTokenInUrl: true
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue



SmtpSettingsvue_type_script_lang_ts.render = SmtpSettingsvue_type_template_id_3c763ca6_render

/* harmony default export */ var SmtpSettings = (SmtpSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=template&id=a3500cc4

const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_3 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_4 = {
  class: "action"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_5 = {
  colspan: "7"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_7 = {
  class: "ui-confirm",
  id: "confirmDeleteAllTrackingFailures"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_8 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_9 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_10 = {
  class: "ui-confirm",
  id: "confirmDeleteThisTrackingFailure"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_11 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_12 = ["value"];
function TrackingFailuresvue_type_template_id_a3500cc4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_FailureRow = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FailureRow");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "matomoTrackingFailures",
    "content-title": _ctx.translate('CoreAdminHome_TrackingFailures')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_TrackingFailuresIntroduction', '2')) + " ", 1), TrackingFailuresvue_type_template_id_a3500cc4_hoisted_1, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "btn deleteAllFailures",
      type: "button",
      onClick: _cache[0] || (_cache[0] = $event => _ctx.deleteAll()),
      value: _ctx.translate('CoreAdminHome_DeleteAllFailures')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.failures.length > 0]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isLoading
    }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[1] || (_cache[1] = $event => _ctx.changeSortOrder('idsite'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Measurable')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[2] || (_cache[2] = $event => _ctx.changeSortOrder('problem'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_Problem')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[3] || (_cache[3] = $event => _ctx.changeSortOrder('solution'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_Solution')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[4] || (_cache[4] = $event => _ctx.changeSortOrder('date_first_occurred'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Date')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[5] || (_cache[5] = $event => _ctx.changeSortOrder('url'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_ColumnPageURL')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[6] || (_cache[6] = $event => _ctx.changeSortOrder('request_url'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_TrackingURL')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_NoKnownFailures')) + " ", 1), TrackingFailuresvue_type_template_id_a3500cc4_hoisted_6], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.failures.length === 0]])]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedFailures, (failure, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: index
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FailureRow, {
        failure: failure,
        onDelete: _cache[7] || (_cache[7] = $event => _ctx.deleteFailure($event.idSite, $event.idFailure))
      }, null, 8, ["failure"])]);
    }), 128))])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ConfirmDeleteAllTrackingFailures')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "yes",
      value: _ctx.translate('General_Yes')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_8), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "no",
      value: _ctx.translate('General_No')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_9)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ConfirmDeleteThisTrackingFailure')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "yes",
      value: _ctx.translate('General_Yes')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "no",
      value: _ctx.translate('General_No')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_12)])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=template&id=a3500cc4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=template&id=62acb18a

const FailureRowvue_type_template_id_62acb18a_hoisted_1 = ["href"];
const FailureRowvue_type_template_id_62acb18a_hoisted_2 = {
  class: "datetime"
};
const FailureRowvue_type_template_id_62acb18a_hoisted_3 = ["title"];
const FailureRowvue_type_template_id_62acb18a_hoisted_4 = ["title"];
function FailureRowvue_type_template_id_62acb18a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.site_name) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Id')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.idsite) + ")", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.problem), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.solution) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noopener noreferrer",
    href: _ctx.failure.solution_url
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_LearnMore')), 9, FailureRowvue_type_template_id_62acb18a_hoisted_1), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.failure.solution_url]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FailureRowvue_type_template_id_62acb18a_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.pretty_date_first_occurred), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.url), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    onClick: _cache[0] || (_cache[0] = $event => _ctx.showFullRequestUrl = true),
    title: _ctx.translate('CoreHome_ClickToSeeFullInformation')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.limtedRequestUrl) + "...", 9, FailureRowvue_type_template_id_62acb18a_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.showFullRequestUrl]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.request_url), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.failure.showFullRequestUrl]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "table-action icon-delete",
    onClick: _cache[1] || (_cache[1] = $event => _ctx.deleteFailure(_ctx.failure.idsite, _ctx.failure.idfailure)),
    title: _ctx.translate('General_Delete')
  }, null, 8, FailureRowvue_type_template_id_62acb18a_hoisted_4)])], 64);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=template&id=62acb18a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=script&lang=ts

/* harmony default export */ var FailureRowvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    failure: {
      type: Object,
      required: true
    }
  },
  emits: ['delete'],
  data() {
    return {
      showFullRequestUrl: false
    };
  },
  computed: {
    limtedRequestUrl() {
      return this.failure.request_url.substring(0, 100);
    }
  },
  methods: {
    deleteFailure(idSite, idFailure) {
      this.$emit('delete', {
        idSite,
        idFailure
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue



FailureRowvue_type_script_lang_ts.render = FailureRowvue_type_template_id_62acb18a_render

/* harmony default export */ var FailureRow = (FailureRowvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=script&lang=ts



/* harmony default export */ var TrackingFailuresvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    FailureRow: FailureRow
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data() {
    return {
      failures: [],
      sortColumn: 'idsite',
      sortReverse: false,
      isLoading: false
    };
  },
  created() {
    this.fetchAll();
  },
  methods: {
    changeSortOrder(columnToSort) {
      if (this.sortColumn === columnToSort) {
        this.sortReverse = !this.sortReverse;
      } else {
        this.sortColumn = columnToSort;
      }
    },
    fetchAll() {
      this.failures = [];
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'CoreAdminHome.getTrackingFailures',
        filter_limit: '-1'
      }).then(failures => {
        this.failures = failures;
        this.isLoading = false;
      }).finally(() => {
        this.isLoading = false;
      });
    },
    deleteAll() {
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteAllTrackingFailures', {
        yes: () => {
          this.failures = [];
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'CoreAdminHome.deleteAllTrackingFailures'
          }).then(() => {
            this.fetchAll();
          });
        }
      });
    },
    deleteFailure(idSite, idFailure) {
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteThisTrackingFailure', {
        yes: () => {
          this.failures = [];
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'CoreAdminHome.deleteTrackingFailure',
            idSite,
            idFailure
          }).then(() => {
            this.fetchAll();
          });
        }
      });
    }
  },
  computed: {
    sortedFailures() {
      const {
        sortColumn
      } = this;
      const sorted = [...this.failures];
      if (this.sortReverse) {
        sorted.sort((lhs, rhs) => {
          if (lhs[sortColumn] > rhs[sortColumn]) {
            return -1;
          }
          if (lhs[sortColumn] < rhs[sortColumn]) {
            return 1;
          }
          return 0;
        });
      } else {
        sorted.sort((lhs, rhs) => {
          if (lhs[sortColumn] < rhs[sortColumn]) {
            return -1;
          }
          if (lhs[sortColumn] > rhs[sortColumn]) {
            return 1;
          }
          return 0;
        });
      }
      return sorted;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue



TrackingFailuresvue_type_script_lang_ts.render = TrackingFailuresvue_type_template_id_a3500cc4_render

/* harmony default export */ var TrackingFailures = (TrackingFailuresvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/



// selfwatch: the JavaScript/image tracking-code generator components were removed
// (no web-analytics tracking). The compiled dist retains inert dead code until rebuilt.

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=CoreAdminHome.umd.js.map