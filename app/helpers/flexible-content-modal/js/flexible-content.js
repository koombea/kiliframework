if (!acf.fields.flexible_content) {
    (function($) {
      acf.fields.flexible_content = acf.field.extend({
        type: "flexible_content",
        $el: null,
        $input: null,
        $values: null,
        $clones: null,

        actions: {
          ready: "initialize",
          append: "initialize",
          show: "show"
        },

        events: {
          'click [data-name="add-layout"]': "_open",
          'click [data-name="remove-layout"]': "_remove",
          'click [data-name="collapse-layout"]': "_collapse",
          "mouseenter .acf-fc-layout-handle": "_mouseenter"
        },

        focus: function() {
          // vars
          this.$el = this.$field.find(".acf-flexible-content:first");
          this.$input = this.$el.children("input");
          this.$values = this.$el.children(".values");
          this.$clones = this.$el.children(".clones");

          // get options
          this.o = acf.get_data(this.$el);

          // min / max
          this.o.min = this.o.min || 0;
          this.o.max = this.o.max || 0;
        },

        count: function() {
          return this.$values.children(".layout").length;
        },

        initialize: function() {
          // disable clone
          acf.disable_form(this.$clones, "flexible_content");

          // render
          this.render();
        },

        show: function() {
          this.$values.find(".acf-field:visible").each(function() {
            acf.do_action("show_field", $(this));
          });
        },

        render: function() {
          // vars
          var self = this;

          // update order numbers
          this.$values.children(".layout").each(function(i) {
            $(this)
              .find("> .acf-fc-layout-handle .acf-fc-layout-order")
              .html(i + 1);
          });

          // empty?
          if (this.count() == 0) {
            this.$el.addClass("empty");
          } else {
            this.$el.removeClass("empty");
          }

          // row limit reached
          if (this.o.max > 0 && this.count() >= this.o.max) {
            this.$el.find("> .acf-actions .button").addClass("disabled");
          } else {
            this.$el.find("> .acf-actions .button").removeClass("disabled");
          }
        },

        render_layout_title: function($layout) {
          // vars
          var $input = $layout.children("input");
          var prefix = $input.attr("name").replace("[acf_fc_layout]", "");

          // ajax data
          var ajaxdata = {
            action: "acf/fields/flexible_content/layout_title",
            field_key: this.$field.data("key"),
            i: $layout.index(),
            layout: $input.val(),
            value: acf.serialize($layout, prefix)
          };

          // ajax get title HTML
          $.ajax({
            url: acf.get("ajaxurl"),
            data: acf.prepare_for_ajax(ajaxdata),
            dataType: "html",
            type: "post",
            success: function(html) {
              // bail early if no html
              if (!html) return;

              // update html
              $layout.find("> .acf-fc-layout-handle").html(html);
            }
          });
        },

        validate_add: function(layout) {
          // defaults
          layout = layout || "";

          // vars
          var max = this.o.max,
            count = this.count();

          // vadiate max
          if (max && count >= max) {
            // vars
            var identifier = max == 1 ? "layout" : "layouts",
              s = acf._e("flexible_content", "max");

            // translate
            s = s.replace("{max}", max);
            s = s.replace(
              "{identifier}",
              acf._e("flexible_content", identifier)
            );

            // alert
            alert(s);

            // return
            return false;
          }

          // vadiate max layout
          if (layout) {
            // vars
            var $popup = $(this.$el.children(".tmpl-popup").html()),
              $a = $popup.find('[data-layout="' + layout + '"]'),
              layout_max = parseInt($a.attr("data-max")),
              layout_count = this.$values.children(
                '.layout[data-layout="' + layout + '"]'
              ).length;

            if (layout_max > 0 && layout_count >= layout_max) {
              // vars
              var identifier = layout_max == 1 ? "layout" : "layouts",
                s = acf._e("flexible_content", "max_layout");

              // translate
              s = s.replace("{max}", layout_count);
              s = s.replace("{label}", '"' + $a.text() + '"');
              s = s.replace(
                "{identifier}",
                acf._e("flexible_content", identifier)
              );

              // alert
              alert(s);

              // return
              return false;
            }
          }

          // return
          return true;
        },

        validate_remove: function(layout) {
          // defaults
          layout = layout || "";

          // vars
          var min = this.o.min,
            count = this.count();

          // vadiate min
          if (min > 0 && count <= min) {
            // vars
            var identifier = min == 1 ? "layout" : "layouts",
              s =
                acf._e("flexible_content", "min") +
                ", " +
                acf._e("flexible_content", "remove");

            // translate
            s = s.replace("{min}", min);
            s = s.replace(
              "{identifier}",
              acf._e("flexible_content", identifier)
            );
            s = s.replace("{layout}", acf._e("flexible_content", "layout"));

            // return
            return confirm(s);
          }

          // vadiate min layout
          if (layout) {
            // vars
            var $popup = $(this.$el.children(".tmpl-popup").html()),
              $a = $popup.find('[data-layout="' + layout + '"]'),
              layout_min = parseInt($a.attr("data-min")),
              layout_count = this.$values.children(
                '.layout[data-layout="' + layout + '"]'
              ).length;

            if (layout_min > 0 && layout_count <= layout_min) {
              // vars
              var identifier = layout_min == 1 ? "layout" : "layouts",
                s =
                  acf._e("flexible_content", "min_layout") +
                  ", " +
                  acf._e("flexible_content", "remove");

              // translate
              s = s.replace("{min}", layout_count);
              s = s.replace("{label}", '"' + $a.text() + '"');
              s = s.replace(
                "{identifier}",
                acf._e("flexible_content", identifier)
              );
              s = s.replace(
                "{layout}",
                acf._e("flexible_content", "layout")
              );

              // return
              return confirm(s);
            }
          }

          // return
          return true;
        },

        sync: function() {
          // vars
          var name = "collapsed_" + this.$field.data("key"),
            collapsed = [];

          // populate collapsed value
          this.$values.children(".layout").each(function(i) {
            if ($(this).hasClass("-collapsed")) {
              collapsed.push(i);
            }
          });

          // update
          acf.update_user_setting(name, collapsed.join(","));
        },

        add: function(layout, $before) {
          // defaults
          $before = $before || false;

          // bail early if validation fails
          if (!this.validate_add(layout)) {
            return false;
          }

          // reference
          var $field = this.$field;

          // vars
          var $clone = this.$clones.children(
            '.layout[data-layout="' + layout + '"]'
          );

          // duplicate
          $el = acf.duplicate($clone);

          // enable
          acf.enable_form($el, "flexible_content");

          // hide no values message
          this.$el.children(".no-value-message").hide();

          // add row
          if ($before) {
            $before.before($el);
          } else {
            this.$values.append($el);
          }

          // focus (may have added sub flexible content)
          this.doFocus($field);

          // update order
          this.render();

          // validation
          acf.validation.remove_error(this.$field);

          // sync collapsed order
          this.sync();
        },

        /*
		*  events
		*
		*  these functions are fired for this fields events
		*
		*  @type	function
		*  @date	17/09/2015
		*  @since	5.2.3
		*
		*  @param	e
		*  @return	n/a
		*/

        _mouseenter: function(e) {
          //console.log('_mouseenter');

          // bail early if already sortable
          if (this.$values.hasClass("ui-sortable")) return;

          // bail early if max 1 row
          if (this.o.max == 1) return;

          // reference
          var self = this;

          // sortable
          this.$values.sortable({
            items: "> .layout",
            handle: "> .acf-fc-layout-handle",
            forceHelperSize: true,
            forcePlaceholderSize: true,
            scroll: true,
            start: function(event, ui) {
              acf.do_action("sortstart", ui.item, ui.placeholder);
            },
            stop: function(event, ui) {
              // render
              self.render();

              acf.do_action("sortstop", ui.item, ui.placeholder);
            },
            update: function(event, ui) {
              // trigger change
              self.$input.trigger("change");
            }
          });
        },

        _open: function(e) {
          //console.log('_open');

          // bail early if validation fails
          if (!this.validate_add()) return false;

          // reference
          var self = this;

          // vars
          var $popup = $(this.$el.children(".tmpl-popup").html());

          // count layouts
          var layouts = {};
          this.$values.children(".layout").each(function() {
            var k = $(this).data("layout");
            layouts[k] = layouts[k] ? layouts[k] + 1 : 1;
          });

          // modify popup
          $popup.find("a").each(function() {
            // vars
            var $a = $(this),
              min = $a.data("min") || 0,
              max = $a.data("max") || 0,
              name = $a.data("layout"),
              count = layouts[name] || 0;

            // max
            if (max && count >= max) {
              $a.addClass("disabled");
              return;
            }

            // min
            if (min) {
              // find diff
              var required = min - count,
                s = acf._e("flexible_content", "required"),
                identifier = required == 1 ? "layout" : "layouts",
                // translate
                s = s.replace("{required}", required);
              s = s.replace("{min}", min);
              s = s.replace("{label} ", ""); // remove label since 5.5.0
              s = s.replace(
                "{identifier}",
                acf._e("flexible_content", identifier)
              );

              // limit reached?
              if (required > 0) {
                var $badge = $('<span class="badge"></span>')
                  .attr("title", s)
                  .text(required);
                $a.append($badge);
              }
            }
          });

          // within layout?
          var $layout = null;
          if (e.$el.hasClass("acf-icon")) {
            $layout = e.$el.closest(".layout");
            $layout.addClass("-open");
          }

          // append
          console.log('acaaaaa');
          $("body").append($popup);

          // position
          this.position_popup($popup, e.$el);

          // events
          var event = function(e, layout) {
            // prevent all listeners
            e.preventDefault();
            e.stopImmediatePropagation();

            // remove events
            $popup.off("click", "a", event_y);
            $("body").off("click", event_n);

            // remove tooltip
            $popup.remove();

            // hide controlls?
            if ($layout !== null) {
              $layout.removeClass("-open");
            }

            // callback
            if (layout !== null) {
              self.add(layout, $layout);
            }
          };

          var event_y = function(e) {
            event(e, $(this).attr("data-layout"));
          };

          var event_n = function(e) {
            event(e, null);
          };

          // add events
          $popup.on("click", "a", event_y);
          $("body").on("click", event_n);
        },

        /*
		*  position_popup
		*
		*  This function will position a $popup to another element (button)
		*
		*  @type	function
		*  @date	8/6/17
		*  @since	5.6.0
		*
		*  @param	$popup (element)
		*  @param	$el (element)
		*  @return	n/a
		*/

        position_popup: function($popup, $el) {
          // position
          var tolerance = 10;
          (target_w = $el.outerWidth()),
            (target_h = $el.outerHeight()),
            (target_t = $el.offset().top),
            (target_l = $el.offset().left),
            (popup_w = $popup.outerWidth()),
            (popup_h = $popup.outerHeight());

          // calculate top
          var top = target_t - popup_h,
            left = target_l + target_w / 2 - popup_w / 2;

          // too far top
          if (top - $(window).scrollTop() < tolerance) {
            $popup.addClass("-bottom");
            top = target_t + target_h;

            // default
          } else {
            $popup.addClass("-top");
          }

          // too far left
          if (left < tolerance) {
            $popup.addClass("-right");
            left = target_l;

            // too far right
          } else if (left + popup_w + tolerance > $(window).width()) {
            $popup.addClass("-left");
            left = target_l + target_w - popup_w;
          }

          // update css
          $popup.css({ top: top, left: left });
        },

        _remove: function(e) {
          console.log("flex _remove");

          // reference
          var self = this;

          // vars
          var $layout = e.$el.closest(".layout");

          // add -open class to show controlls
          $layout.addClass("-open");

          // confirm
          acf.tooltip.confirm_remove(e.$el, function(result) {
            if (result) {
              self.remove_layout($layout);
            } else {
              $layout.removeClass("-open");
            }
          });
        },

        remove_layout: function($layout) {
          // reference
          var self = this;

          // bail early if validation fails
          if (!this.validate_remove($layout.attr("data-layout"))) {
            return;
          }

          // vars
          var $message = null,
            end_height = 0;

          // show message if no values
          if (this.count() == 1) {
            $message = this.$el.children(".no-value-message");
            end_height = $message.outerHeight();
          }

          // remove
          acf.remove_el(
            $layout,
            function() {
              // show message
              if ($message !== null) {
                $message.show();
              }

              // update order
              self.render();

              // trigger change to allow attachment save
              self.$input.trigger("change");

              // sync collapsed order
              self.sync();
            },
            end_height
          );
        },

        _collapse: function(e) {
          //console.log('_collapse');

          // vars
          var $layout = e.$el.closest(".layout"),
            collapsed = $layout.hasClass("-collapsed"),
            action = collapsed ? "show" : "hide";

          // render
          // - do this before calling actions to avoif focusing on the wrong field
          this.render_layout_title($layout);

          // toggle class
          $layout.toggleClass("-collapsed");

          // sync collapsed order
          this.sync();

          // action
          acf.do_action(action, $layout, "collapse");
        }
      });
    })(jQuery);
  }