// {literal}
$(document).on("click", ".toggle-love", function(e) {
    e.preventDefault();

    const $btn = $(this);
    const gkid = $btn.data("gkid");
    const isLiked = parseInt($btn.data("liked")) === 1;

    const endpoint = isLiked
        ? "{/literal}{'geokret_unlove'|alias:'@gkid='}{literal}".replace("@gkid=", gkid)
        : "{/literal}{'geokret_love'|alias:'@gkid='}{literal}".replace("@gkid=", gkid);

    const csrfToken = "{/literal}{$f3->get('SESSION.csrf')}{literal}";

    $.ajax({
        url: endpoint,
        type: "POST",
        dataType: "json",
        data: {
            csrf_token: csrfToken
        },
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        success(response) {
            const newIsLiked = !isLiked;

            // Update all buttons for this GeoKret (panel heading + actions panel may both exist)
            function updateBtn($b) {
                $b.data("liked", newIsLiked ? 1 : 0);
                if (newIsLiked) {
                    $b.css("color", "#d9534f");
                    $b.attr("title", "{/literal}{t}Remove your love for this GeoKret{/t}{literal}");
                } else {
                    $b.css("color", "");
                    $b.attr("title", "{/literal}{t}Love this GeoKret{/t}{literal}");
                }
                if (typeof response.loves_count !== "undefined") {
                    var $c = $b.find(".loves-count").length > 0
                        ? $b.find(".loves-count")
                        : $b.siblings(".loves-count");
                    $c.text(response.loves_count);
                }
            }

            $(".toggle-love[data-gkid=\"" + gkid + "\"]").each(function() {
                updateBtn($(this));
            });

            // Show feedback
            const message = newIsLiked ? "{/literal}{t}You have loved this GeoKret! ❤️{/t}{literal}" : "{/literal}{t}You have removed your love for this GeoKret.{/t}{literal}";
            if (typeof GK !== "undefined" && GK.flashMessage) {
                GK.flashMessage(message, newIsLiked ? "success" : "info", 3000);
            }
        },
        error(xhr) {
            if (xhr.status === 401) {
                window.location.href = "{/literal}{'login'|alias}{literal}";
            } else {
                if (typeof GK !== "undefined" && GK.flashMessage) {
                    GK.flashMessage("{/literal}{t}Error toggling love{/t}{literal}", "danger");
                }
            }
        }
    });
});
// {/literal}
