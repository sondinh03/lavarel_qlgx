(self["webpackChunk"] = self["webpackChunk"] || []).push([["/js/custom"],{

/***/ "./resources/js/custom.js":
/*!********************************!*\
  !*** ./resources/js/custom.js ***!
  \********************************/
/***/ (() => {

jQuery(document).ready(function ($) {
  function add_dihoc_k1(a) {
    var newitem = '';
    $('#ky1 thead tr').append('<th class="text-center">' + file_items_k1 + '</th>');
    $('#ky1 tbody tr').each(function (idx, el) {
      var b = $(el).attr('data-id');
      newitem += '<td>';
      newitem += '<input type="hidden" name="id_hv[]" value="' + b + '">';
      newitem += '<div class="text-center">';
      newitem += "<div class=\"form-check form-check-inline\" id=\"dihoc_content_" + file_items_k1 + "_" + b + "\">";
      newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"dihoc[" + b + "][" + file_items_k1 + "]\" required id=\"dihoc_" + file_items_k1 + "_" + b + "\" value=\"1\" />";
      newitem += '<label class="form-check-label" for="dihoc_' + file_items_k1 + '_' + b + '">+</label>';
      newitem += "</div>";
      newitem += "<div class=\"form-check form-check-inline\" id=\"dihoc_content_" + file_items_k1 + "_" + b + "\">";
      newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"dihoc[" + b + "][" + file_items_k1 + "]\" required id=\"dihoc_" + file_items_k1 + "_" + b + "\" value=\"2\" />";
      newitem += '<label class="form-check-label" for="dihoc_' + file_items_k1 + '_' + b + '">-</label>';
      newitem += "</div>";
      newitem += "<div class=\"form-check form-check-inline\" id=\"dihoc_content_" + file_items_k1 + "_" + b + "\">";
      newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"dihoc[" + b + "][" + file_items_k1 + "]\" required id=\"dihoc_" + file_items_k1 + "_" + b + "\" value=\"0\" />";
      newitem += '<label class="form-check-label" for="dihoc_' + file_items_k1 + '_' + b + '">0</label>';
      newitem += "</div>";
      newitem += '</div>';
      newitem += '</td>';
      $('#ky1 tbody tr#chuoi_' + b).append(newitem);
      var newitem = '';
    });
    file_items_k1++;
    return false;
  }
  function add_dihoc_k2(a) {
    var newitem = '';
    $('#ky2 thead tr').append('<th class="text-center">' + file_items_k2 + '</th>');
    $('#ky2 tbody tr').each(function (idx, el) {
      var b = $(el).attr('data-id');
      newitem += '<td>';
      newitem += '<input type="hidden" name="id_hv[]" value="' + b + '">';
      newitem += '<div class="text-center">';
      newitem += "<div class=\"form-check form-check-inline\" id=\"dihoc_content_" + file_items_k2 + "_" + b + "\">";
      newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"dihoc[" + b + "][" + file_items_k2 + "]\" required id=\"dihoc_" + file_items_k2 + "_" + b + "\" value=\"1\" />";
      newitem += '<label class="form-check-label" for="dihoc_' + file_items_k2 + '_' + b + '">+</label>';
      newitem += "</div>";
      newitem += "<div class=\"form-check form-check-inline\" id=\"dihoc_content_" + file_items_k2 + "_" + b + "\">";
      newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"dihoc[" + b + "][" + file_items_k2 + "]\" required id=\"dihoc_" + file_items_k2 + "_" + b + "\" value=\"2\" />";
      newitem += '<label class="form-check-label" for="dihoc_' + file_items_k2 + '_' + b + '">-</label>';
      newitem += "</div>";
      newitem += "<div class=\"form-check form-check-inline\" id=\"dihoc_content_" + file_items_k2 + "_" + b + "\">";
      newitem += "<input class=\"form-check-input\" type=\"radio\" name=\"dihoc[" + b + "][" + file_items_k2 + "]\" required id=\"dihoc_" + file_items_k2 + "_" + b + "\" value=\"0\" />";
      newitem += '<label class="form-check-label" for="dihoc_' + file_items_k2 + '_' + b + '">0</label>';
      newitem += "</div>";
      newitem += '</div>';
      newitem += '</td>';
      $('#ky2 tbody tr#chuoi_' + b).append(newitem);
      var newitem = '';
    });
    file_items_k2++;
    return false;
  }
});

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./resources/js/custom.js"));
/******/ }
]);