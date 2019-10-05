{* Taken from https://www.youtube.com/watch?v=iAD94CYPzRo *}
<script>
    window.onload = function() {
        window.jQuery ? $(document).ready(function() {
            $(".sidebarNavigation .navbar-collapse").hide().clone().appendTo("body").removeAttr("class").addClass("sideMenu").show(),
                $("body").append("<div class='sideMenu-overlay'></div>"),
                $(".navbar-toggle").on("click", function() {
                    $(".sideMenu").addClass($(".sidebarNavigation").attr("data-sidebarClass")),
                        $(".sideMenu, .sideMenu-overlay").toggleClass("open"),
                        $(".sideMenu-overlay").on("click", function() {
                            $(this).removeClass("open"), $(".sideMenu").removeClass("open")
                        })
                }), $(window).resize(function() {
                    $(".navbar-toggle").is(":hidden") ? $(".sideMenu, .sideMenu-overlay").hide() : $(".sideMenu, .sideMenu-overlay").show()
                })
        }) : console.log("sidebarNavigation Requires jQuery")
    };
</script>
