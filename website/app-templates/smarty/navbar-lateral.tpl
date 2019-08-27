{* Taken from https://www.youtube.com/watch?v=iAD94CYPzRo *}
<style>
    body {
        position: relative
    }

    .overlay,
    .sideMenu {
        position: fixed;
        bottom: 0
    }

    .overlay {
        top: 0;
        left: -100%;
        right: 100%;
        margin: auto;
        background-color: rgba(0, 0, 0, .4);
        z-index: 998;
        transition: all ease 20ms
    }

    .sideMenu,
    .sidebarNavigation {
        z-index: 999;
        margin-bottom: 0
    }

    .overlay.open {
        left: 0;
        right: 0
    }

    .sidebarNavigation .left-navbar-toggle {
        float: left;
        margin-right: 0;
        margin-left: 15px
    }

    .sideMenu {
        left: -100%;
        top: 50px;
        transition: all ease-in-out .4s;
        overflow: hidden;
        width: 70%;
        max-width: 300px;
    }

    .sideMenu.open {
        left: 0;
        display: block;
        overflow-y: auto
    }

    .sideMenu ul {
        margin: 0
    }
</style>

<script>
    window.onload = function() {
        window.jQuery ? $(document).ready(function() {
            $(".sidebarNavigation .navbar-collapse").hide().clone().appendTo("body").removeAttr("class").addClass("sideMenu").show(),
                $("body").append("<div class='overlay'></div>"),
                $(".navbar-toggle").on("click", function() {
                    $(".sideMenu").addClass($(".sidebarNavigation").attr("data-sidebarClass")),
                        $(".sideMenu, .overlay").toggleClass("open"),
                        $(".overlay").on("click", function() {
                            $(this).removeClass("open"), $(".sideMenu").removeClass("open")
                        })
                }), $(window).resize(function() {
                    $(".navbar-toggle").is(":hidden") ? $(".sideMenu, .overlay").hide() : $(".sideMenu, .overlay").show()
                })
        }) : console.log("sidebarNavigation Requires jQuery")
    };
</script>
