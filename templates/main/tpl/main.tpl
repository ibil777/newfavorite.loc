<!DOCTYPE HTML>
<html>
<head>
    <meta charset=utf-8>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name=author content='Design and conceptual - Igor Boychenko, ibil.com.ua'>
    <meta name='programming' content='Jaroslav Poleschuk, www.webpro.cimis.com.ua'>
    <meta name='description' content='[DESCRIPTION]'>
    <meta name='keywords' content='[KEYWORDS]'>
    <link href='[BASE_HREF]/[TPL_DIR]css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='[BASE_HREF]/[TPL_DIR]css/font-awesome.min.css' rel='stylesheet' type='text/css'>
    <link href='[BASE_HREF]/[TPL_DIR]css/go_top.css' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="[BASE_HREF]/owlcarousel/assets/owl.carousel.css">
    <link rel="stylesheet" href="[BASE_HREF]/owlcarousel/assets/owl.theme.default.css">
    <link href='[BASE_HREF]/[TPL_DIR]css/tpl.min.css' rel='stylesheet' type='text/css'>
    <link href='nf.ico' rel='shortcut icon' type="image/x-icon">
    <script src="[BASE_HREF]/include/js/jquery.min.js"></script>
    <script src="[BASE_HREF]/[TPL_DIR]/js/bootstrap.bundle.min.js"></script>
    <script src="[BASE_HREF]/[TPL_DIR]/js/go_top.js"></script>
    <script src="[BASE_HREF]/owlcarousel/owl.carousel.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $(".owl-carousel").owlCarousel({
                items: 1,
                autoplay: true,
                slideSpeed: true,
                autoTimeoute: 4500,
                loop: true,
                mouseDrag: false,
                singleItem: true,
                dots: true,
                nav: false,
            });

            [ONLOAD]
        });
    </script>

    <title>[TITLE]</title>

    [HEAD]

</head>

<body class="container-fluid">
    <section id="header" class="row justify-content-center">
        <div class="header container">
            <div class="row">
                <div class="col-12 col-md-6">
                    <a href="/"><img src='[IMAGE_DIR]newfavorite_titul.jpg' alt="Новый фаворит"></a>
                </div>
                <div class="col-12 col-md-6">
                    <div class="row justify-content-end">
                        <div class="calendar">
                                <script src='templates/main/js/calendar.js'></script>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <form class="search d-flex justify-content-between align-items-center" name="search_form" method="post" action="search.htm">
                            <input class="search_input" type="text" name="search_query" value="">
                            <button type="submit" value="" name="search" class="btn btn-secondary search-button">
                                <i class="fa fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <nav class="menu" role="navigation">
                    [MAIN_MENU]
                </nav>
            </div>
        </div>
        <div class="line-white"></div>
        <div class="line-gray"></div>
        <div class="line-white"></div>
    </section>
    <section id="contentwrapper" class="row justify-content-center">
        <div class="container-fluid">
            <div class="row bredcrumbs">
                [BRANCH]
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div id="content" class="col-12 col-md-10" role="main">
                    [PANEL_SLIDER][PANEL_CONTENT][CONTENT]
                </div>
                <aside id="right-bar" class="col-12 col-md-2" role="complementary">
                    <div class="right-bar-cont">
                        <div role="navigation">[SUB_MENU]</div>
                        <div>[PANEL_SCRIPT]</div>
                        <div>[PANEL_ADVRIGHT]</div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <div class="clr"></div>

    <footer class="footer">
        <div class="footer-top">
            <div class="socpanel">[PANEL_SOCBUTTON]</div>
        </div>
        <div class="clr"></div>
        <div class="line-white"></div>
        <div class="line-gray"></div>
        <div class="line-white"></div>
        <div class="ad"></div>
        <div class="copyrightwrapper">
            <div class="stat">
                <div class="statwrapper">
                </div>
            </div>
        </div>
        <div class="copyleft" role="contentinfo">[PANEL_COPYLEFT]</div>
        <div class="copyright">[PANEL_COPYRIGHT]</div>
    </footer>
</body>
</html>