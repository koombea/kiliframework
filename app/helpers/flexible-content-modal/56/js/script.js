// Modal window
function acf_fc_modal_init() {
  jQuery(".acf-flexible-content .layout:not(.acf-clone)").each(function() {
    var a = jQuery(this);
    if (a.parents(".acf-fields").length > 1) return !0;
    a.addClass("fc-modal"),
      a.find("> .acf-fc-layout-handle").off("click"),
      a.find("> .acf-fc-layout-controlls > .-collapse").remove(),
      a.find("> .acf-fc-layout-controlls > .-plus").remove();
    var l = a.find("> .acf-fc-layout-controlls > .-minus");
    0 == l.siblings(".-pencil").length &&
      (l.before(
        '<a class="acf-icon -pencil small" href="#" data-event="edit-layout" title="Edit layout"></a>'
      ),
      a
        .find("> .acf-fc-layout-controlls a.-pencil")
        .on("click", acf_fc_modal_open)),
      0 == a.find("> .acf-fc-modal-title").length &&
        (a.prepend('<div class="acf-fc-modal-title"></div>'),
        a
          .find("> .acf-fields, > .acf-table")
          .wrapAll('<div class="acf-fc-modal-content"></div>'));
  }),
    jQuery(".kiliframework").addClass("visible");
}
function acf_fc_modal_open() {
  var a = jQuery(this).parents(".layout");
  if (!a.hasClass("-modal")) {
    a.removeClass("-collapsed");
    var l = a.find("> .acf-fc-layout-handle").html();
    a
      .find(".acf-fc-modal-title")
      .html(
        l +
          '<a class="acf-icon -cancel" href="javascript:acf_fc_modal_remove()"></a><input name="save" type="submit" class="acf-button-update button" id="publish-kili" value="Update">'
      ),
      a.addClass("-modal"),
      jQuery("body").append("<div id='TB_overlay'></div>"),
      jQuery("#TB_overlay").click(acf_fc_modal_remove),
      jQuery("body").addClass("acf-modal-open"),
      jQuery(".color-picker-wrapper").length < 1 &&
        jQuery(".fc-modal .wp-color-result").wrap(
          '<span class="color-picker-wrapper"></span>'
        );
  }
}
function acf_fc_modal_remove() {
  jQuery("body").removeClass("acf-modal-open"),
    jQuery(
      ".acf-flexible-content .layout.-modal > .acf-fc-layout-handle"
    ).click(),
    jQuery(".acf-flexible-content .layout").removeClass("-modal"),
    jQuery("#TB_overlay").remove();
}
jQuery(document).ready(function() {
  try {
    //ACF Pro
    if (acf && acf.fields.flexible_content) {
      var a = acf.fields.flexible_content.render;
      if (a) {
        acf.fields.flexible_content.render = function() {
          acf_fc_modal_init();
          return a.apply(this, arguments);
        };
      }
    }
    acf_fc_modal_init();
  } catch (a) {
    console.log(a);
  }
});