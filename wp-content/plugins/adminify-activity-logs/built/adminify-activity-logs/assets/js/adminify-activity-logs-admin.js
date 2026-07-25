(function ($) {
    // Save Days Data
    $("#activity-filter").on("click", "#adminify-activity-logs-days-save", function (e) {
        e.preventDefault();
        var days_value = $("#adminify-activity-logs-days").val();

        $("#adminify-activity-logs-days-save").text("Saving...");

        jQuery.ajax({
            url: ACTIVITYLOGSCORE.admin_ajax,
            type: "POST",
            data: {
                action: "adminify_activitylogs_store_days",
                activity_days: days_value,
                security: ACTIVITYLOGSCORE.recommended_nonce,
            },
            success: function (response) {
                if (response.data.message == "ok") {
                    setTimeout(() => {
                        $("#adminify-activity-logs-days-save").text("Save Settings");
                    }, 1200);
                }
            },
        });
    });

    // Notice Hide
    $("body").on("click", ".adminify-activity-logs-upgrade-popup .popup-dismiss", function (evt) {
        evt.preventDefault();
        $(this).closest(".adminify-activity-logs-upgrade-popup").fadeOut(200);
    });

    // Notice Show
    $("body").on("click", ".adminify-activity-logs-disabled", function (evt) {
        evt.preventDefault();
        $(".adminify-activity-logs-upgrade-popup").fadeIn(200);
    });

    // Install WP Adminify Plugin
    $("body").on(
        "click",
        ".install-adminify-adminify-activity-logs-now .install-now",
        function (e) {
            e.preventDefault();
            if (!$(this).hasClass("updating-message")) {
                let plugin = $(this).attr("data-plugin");
                installAdminifyPlugin($(this), plugin);
            }
        }
    );

    function installAdminifyPlugin(element, plugin) {
        element.addClass("updating-message");
        element.text("Installing...");
        jQuery.ajax({
            url: ACTIVITYLOGSCORE.admin_ajax,
            type: "POST",
            data: {
                action: "adminify_activitylogs_install_plugin",
                type: "install",
                plugin: plugin,
                nonce: ACTIVITYLOGSCORE.recommended_nonce,
            },
            success: function (response) {
                console.log("response", response);

                setTimeout(() => {
                    element.removeClass("updating-message");
                    element.text("Activated");
                }, 1000);
            },
        });
    }

    // Tab
    $(".filter-links").on("click", "a", function (e) {
        e.preventDefault();
        let cls = $(this).data("type");
        $(this).addClass("current").parent().siblings().find("a").removeClass("current");
        $("#the-list .plugin-card").each(function (i, el) {
            if (cls == "all") {
                $(this).removeClass("hide");
            } else {
                if ($(this).hasClass(cls)) {
                    $(this).removeClass("hide");
                } else {
                    $(this).addClass("hide");
                }
            }
        });
    });

    // Search
    $(".adminify-activity-logs-search-plugins #search-plugins").on("keyup", function () {
        var value = $(this).val();
        var srch = new RegExp(value, "i");
        $("#the-list .plugin-card").each(function () {
            var $this = $(this);
            if (!($this.find(".name h3 a, .desc p").text().search(srch) >= 0)) {
                $this.addClass("hide");
            }
            if ($this.find(".name h3 a, .desc p").text().search(srch) >= 0) {
                $this.removeClass("hide");
            }
        });
    });
})(jQuery);
