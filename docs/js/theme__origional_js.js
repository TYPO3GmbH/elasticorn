$(function () {
    "use strict";

    $(document).ready(function () {
        init_scroll();
        int_introHeight();
        stickHeader();
        int_nav_menu_height();
        int_sliderHero();
        int_isotopGridPortfolio();
        int_Elements();
        int_SliderPluguns();
        int_lightbox();
        init_wow();
    });

    $(window).load(function () {
        int_introHeight();
        int_isotopGridPortfolio();

        // Site Preloader
        $('#preloader').fadeOut('slow', function () {
            $(this).remove();
        });

    });

    $(window).resize(function () {
        int_introHeight();
        stickHeader();
        int_nav_menu_height();
        int_SliderPluguns();
        int_lightbox();
    });

    $(window).scroll(function () {
        stickHeader();
    });

    // ---------------------------------------------------------------------------------------------------------------------------->
    // SCROLL FUNCTIONS   ||-----------
    // ---------------------------------------------------------------------------------------------------------------------------->

    function init_scroll() {

        $('.scroll-top').click(function () {
            $('html, body').animate({ scrollTop: 0 }, 2000);
            return false;
        });

        // Scroll Down Elements
        $('.scroll-down[href^="#"], .scroll-to-target[href^="#"]').on('click', function (e) {
            e.preventDefault();

            var target = this.hash;
            var $target = $(target);

            $('html, body').stop().animate({
                'scrollTop': $target.offset().top
            }, 900, 'swing', function () {
                window.location.hash = target;
            });
        });



    };

    // ----------------------------------------------------------------
    // Backgrounds Image (Slider, Section, etc..)
    // ----------------------------------------------------------------
    var pageSection = $('.slide-img, .bg-image');
    pageSection.each(function (indx) {

        if ($(this).attr("data-background-img")) {
            $(this).css("background-image", "url(" + $(this).data("background-img") + ")");
        }
    });


    // ----------------------------------------------------------------
    // Intro Height
    // ----------------------------------------------------------------
    function int_introHeight() {

        var targetHeight = '.full-height, #hero-carousel .slide';
        var windiwHeight = $(window).height();
        var borderHeightMinus = -60;
        var borderHeightMinusMobile = -30;

        // Intro Height
        if ($(window).width() < 480) {
            $('.intro-fullscreen, .intro-full-height').css('height', windiwHeight);
        }
        else {
            $('.intro-fullscreen, .intro-full-height').css({
                'height': windiwHeight,
                'min-height': '500px'
            });
        }
    };

    // ----------------------------------------------------------------
    // Intro Height
    // ----------------------------------------------------------------

    function stickHeader() {
        var scrolled = $(window).scrollTop();
        var windHeight = $(window).height();
        if (scrolled > 100) {
            $('.header').addClass('header-prepare');
        } else {
            $('.header').removeClass('header-prepare');
        }

        if (scrolled > 1) {
            $('.header').addClass('header-fixed');
        } else {
            $('.header').removeClass('header-fixed');
        }
    };

    // ----------------------------------------------------------------
    // Navigation Menu panel
    // ----------------------------------------------------------------

    var mobile_menu_icon = $(".nav-menu-icon");
    var mobile_menu = $(".nav-menu");

    // Mobile menu max height
    function int_nav_menu_height() {
        mobile_menu.css("max-height", $(window).height() - 65 + "px");
    };

    // Mobile menu style toggle
    $(".nav-menu-icon").click(function () {
        mobile_menu_icon.toggleClass('active');
        mobile_menu.toggleClass('active');
        return false;
    });


    // ----------------------------------------------------------------
    // Slider Hero
    // ----------------------------------------------------------------
    function int_sliderHero() {

        // Slider Default
        $('.intro-tp-banner').revolution({
            delay: 15000,
            startwidth: 991,
            startheight: 500,
            hideThumbs: 10,
            fullWidth: "off",
            fullScreen: "on",
            fullScreenOffsetContainer: "",
            navigationType: "none"
        });

        // Slider Vertical
        $('.intro-tp-banner-vertical').show().revolution(
            {
                dottedOverlay: "none",
                delay: 9000,
                startwidth: 991,
                startheight: 700,
                hideThumbs: 200,
                hideTimerBar: "on",

                navigationType: "none",

                touchenabled: "on",
                onHoverStop: "on",

                swipe_velocity: 0.7,
                swipe_min_touches: 1,
                swipe_max_touches: 1,
                drag_block_vertical: false,


                keyboardNavigation: "on",

                navigationHAlign: "center",
                navigationVAlign: "bottom",
                navigationHOffset: 0,
                navigationVOffset: 20,

                soloArrowLeftHalign: "left",
                soloArrowLeftValign: "center",
                soloArrowLeftHOffset: 20,
                soloArrowLeftVOffset: 0,

                soloArrowRightHalign: "right",
                soloArrowRightValign: "center",
                soloArrowRightHOffset: 20,
                soloArrowRightVOffset: 0,

                shadow: 0,
                fullWidth: "off",
                fullScreen: "on",

                spinner: "spinner0",

                stopLoop: "off",
                stopAfterLoops: -1,
                stopAtSlide: -1,

                shuffle: "off",


                forceFullWidth: "off",
                fullScreenAlignForce: "off",
                minFullScreenHeight: "400",

                hideThumbsOnMobile: "off",
                hideNavDelayOnMobile: 1500,
                hideBulletsOnMobile: "off",
                hideArrowsOnMobile: "off",
                hideThumbsUnderResolution: 0,

                hideSliderAtLimit: 0,
                hideCaptionAtLimit: 0,
                hideAllCaptionAtLilmit: 0,
                startWithSlide: 0
            });

        // Slider KenBurns
        $('.intro-tp-banner-kenBurns').revolution({

            delay: 9000,
            startwidth: 991,
            startheight: 500,
            hideThumbs: 200,
            shadow: 0,
            fullWidth: "off",
            fullScreen: "on",
            navigationType: "none"
        });
    };


    // ----------------------------------------------------------------
    // JS Plugins
    // ----------------------------------------------------------------

    // Parallax Function element
    $(window).stellar({
        responsive: true,
        positionProperty: 'position',
        horizontalScrolling: false

    });

    //Parallax Function element
    $('.parallax').each(function () {
        var $el = $(this);
        $(window).scroll(function () {
            parallax($el);
        });
        parallax($el);
    });
    function parallax($el) {
        var diff_s = $(window).scrollTop();
        var parallax_height = $('.parallax').height();
        var yPos_p = (diff_s * 0.5);
        var yPos_m = -(diff_s * 0.5);
        var diff_h = diff_s / parallax_height;

        if ($('.parallax').hasClass('parallax-section1')) {
            $el.css('top', yPos_p);
        }
        if ($('.parallax').hasClass('parallax-section2')) {
            $el.css('top', yPos_m);
        }
        if ($('.parallax').hasClass('parallax-static')) {
            $el.css('top', (diff_s * 1));
        }
        if ($('.parallax').hasClass('parallax-opacity')) {
            $el.css('opacity', (1 - diff_h * 1));
        }

        if ($('.parallax').hasClass('parallax-background1')) {
            $el.css("background-position", 'left' + " " + yPos_p + "px");
        }
        if ($('.parallax').hasClass('parallax-background2')) {
            $el.css("background-position", 'left' + " " + -yPos_p + "px");

        }
    };


    // Isotope Portfolio Grid
    function int_isotopGridPortfolio() {

        var $container = $('.portfolio-grid-fit');
        $container.isotope({
            itemSelector: '.portfolio-item',
            layoutMode: 'fitRows',
            transitionDuration: '.8s'
        })

        // bind filter button click
        $('.portfolio-filter').on('click', '.categories', function () {
            var filterValue = $(this).attr('data-filter');
            $container.isotope({ filter: filterValue });
        });

        // change active class on categories
        $('.categories-filter').each(function (i, buttonGroup) {
            var $buttonGroup = $(buttonGroup);
            $buttonGroup.on('click', '.categories', function () {
                $buttonGroup.find('.active').removeClass('active');
                $(this).addClass('active');
            });

        });
    };

    // ----------------------------------------------------------------
    // Elements And Shortcode
    // ----------------------------------------------------------------
    function int_Elements() {

        // Skills Progressbar Function
        $(".skillbar").appear(function () {
            $('.skillbar').each(function () {
                $(this).find('.skillbar-bar-child').animate({
                    width: $(this).attr('data-percent')
                }, 2000);
            });
        });

        // Tabs Function
        //$(".tabs-nav  li a").click(function () {
        //    var tab_height = $(".tabs-cont > .tab-pane").filter($(this).attr("href")).height();
        //    $(".tabs-cont").animate({
        //        height: tab_height
        //    }, function () {
        //        $(".tabs-cont").css("height", "auto");
        //    });
        //});

        // Accordion Function
        var accordionAllContent = $(".accordion > .accordion-content").hide();
        accordionAllContent.first().slideDown("easeOutExpo");
        $(".accordion > .accordion-title > a").first().addClass("active");
        $(".accordion > .accordion-title > a").click(function () {
            var current = $(this).parent().next(".accordion-content");
            $(".accordion > .accordion-title > a").removeClass("active");
            $(this).addClass("active");
            accordionAllContent.not(current).slideUp("easeInExpo");
            $(this).parent().next().slideDown("easeOutExpo");

            return false;
        });

        // Toggle Function
        var togglesAllContent = $(".toggle > .toggle-content").hide();
        $(".toggle > .toggle-title > a").click(function () {

            if ($(this).hasClass("active")) {

                $(this).parent().next().slideUp("easeOutExpo");
                $(this).removeClass("active");

            }
            else {
                var current = $(this).parent().next(".toggle-content");
                $(this).addClass("active");
                $(this).parent().next().slideDown("easeOutExpo");
            }

            return false;
        });

        // Tooltip Function
        $(".tipped").tipper();


        //Counter Number Function
        $(".counter-num").appear(function () {
            var counter = $(this);
            counter.countTo({
                from: 0,
                to: counter.html(),
                speed: 1300,
                refreshInterval: 60,
            });

        });

        // Responsive Media Elements
        $(".media").fitVids();

    };


    // ----------------------------------------------------------------
    // Slider & Sarousel Plugins
    // ----------------------------------------------------------------
    function int_SliderPluguns() {

        // hero Text Slider
        $('.intro-text-slider').owlCarousel({
            slideSpeed: 400,
            singleItem: true,
            autoHeight: false,
            navigation: true,  // Show next and prev buttons
            pagination: true,  // Show pagination buttons
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
            responsive: true

        });

        // Team Carousel
        $('.team-carousel').owlCarousel({
            autoPlay: false,
            stopOnHover: true,
            items: 4,
            itemsDesktop: [1170, 3],
            itemsDesktopSmall: [1000, 2],
            itemsTabletSmall: [768, 1],
            itemsMobile: [480, 1],
            pagination: false,  // Hide pagination buttons
            navigation: false,  // Hide next and prev buttons
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"]
        });

        // Testimonial Carousel
        $('.testimonial-carousel').owlCarousel({
            autoPlay: true,
            autoHeight: true,
            stopOnHover: true,
            singleItem: true,
            slideSpeed: 350,
            pagination: true,  // Show pagination buttons
            navigation: false,  // Hide next and prev buttons
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
            //  responsive: true
        });

        // Content Carousel
        $('.content-carousel').owlCarousel({
            autoPlay: true,
            autoHeight: true,
            stopOnHover: true,
            singleItem: true,
            slideSpeed: 500,
            pagination: false,  // Hide pagination buttons
            navigation: true,   // Show next and prev buttons
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
            responsive: true
        });

        // Client Carousel
        $('.client-carousel').owlCarousel({
            autoPlay: 2500,
            stopOnHover: true,
            items: 6,
            itemsDesktop: [1170, 5],
            itemsDesktopSmall: [1024, 4],
            itemsTabletSmall: [768, 3],
            itemsMobile: [480, 2],
            pagination: false,  // hide pagination buttons
            navigation: false,  // hide next and prev buttons
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"]
        });

        // Image Carousel
        $('.image-carousel').owlCarousel({
            navigation: true,  // Show next and prev buttons
            pagination: true,  // Show pagination buttons
            slideSpeed: 350,
            paginationSpeed: 400,
            singleItem: true,
            navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
            autoPlay: false,
            autoHeight: true,
            responsive: true
        });

    };


    // ----------------------------------------------------------------
    // lightbox Plugins (image lightbox, iframe lightbox, video lightbox)
    // ----------------------------------------------------------------
    function int_lightbox() {

        // Portfolio Gallery Popup
        $('.gallery-popup').magnificPopup({
            delegate: '.gallery-popup-link',
            type: 'image',
            tLoading: 'Loading image #%curr%...',
            mainClass: 'mfp-img-mobile',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
            },
            image: {
                tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
                titleSrc: function (item) {
                    return item.el.attr('title');
                }
            }
        });

        // Video popup (youtube, vimeo), map popup, iframe popup,
        $('.popup-youtube, .popup-vimeo, .popup-gmaps, .video-popup').magnificPopup({
            disableOn: 700,
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
        });
    };

    // ----------------------------------------------------------------
    // Onepage Nav Elements
    // ----------------------------------------------------------------
    $('.singlepage-nav').singlePageNav({
        offset: 0,
        filter: ':not(.nav-external)',
        updateHash: true,
        currentClass: 'current',
        easing: 'swing',
        speed: 750,
        beforeStart: function () {
            if ($(window).width() < 1024) {
                $('.nav-menu-icon').removeClass('active');
                $('.nav-menu').removeClass('active');
            };
        }
    });


    // ----------------------------------------------------------------
    // WOW animations
    // ----------------------------------------------------------------
    function init_wow() {
        $(function () {
            var wow = new WOW({
                boxClass: 'wow',
                animateClass: 'animated',
                offset: 90,
                mobile: false,
                live: true
            });
            wow.init();
        });
    }

});


// ---------------------------------
// Extra Jquery
// ---------------------------------
$(window).load(function () {
    $(".all-demos-link").attr("href", "../demo.html");
});


