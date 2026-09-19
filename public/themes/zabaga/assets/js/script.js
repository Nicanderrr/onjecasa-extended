(function ($) {
  "use strict";

  // Cache frequently used DOM elements
  const $window = $(window);
  const $document = $(document);
  const $body = $('body');

  /*--------------------------------------------------------------
    RegisterPlugin, ScrollTrigger, SplitText
  --------------------------------------------------------------*/
  function initializeGSAP() {
    try {
      if (typeof gsap !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger, SplitText);
        gsap.config({
          nullTargetWarn: false,
          trialWarn: false
        });
      }
    } catch (error) {
      console.warn('GSAP initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Throttle function for performance optimization
  --------------------------------------------------------------*/
  function throttle(func, delay) {
    let timeoutId;
    let lastExecTime = 0;

    return function (...args) {
      const currentTime = Date.now();

      if (currentTime - lastExecTime > delay) {
        func.apply(this, args);
        lastExecTime = currentTime;
      } else {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
          func.apply(this, args);
          lastExecTime = Date.now();
        }, delay - (currentTime - lastExecTime));
      }
    };
  }

  /*--------------------------------------------------------------
    Debounce function for performance optimization
  --------------------------------------------------------------*/
  function debounce(func, delay) {
    let timeoutId;

    return function (...args) {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
  }

  /*--------------------------------------------------------------
    Full Height
  --------------------------------------------------------------*/
  function fullHeight() {
    try {
      $('.full-height').css("height", $(window).height());
    } catch (error) {
      console.warn('Full height calculation failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Swiper Initialization
  --------------------------------------------------------------*/
  function initSwiper() {
    try {
      if (typeof Swiper === 'undefined') {
        console.warn('Swiper library not loaded');
        return;
      }

      const $swiperSliders = $(".thm-swiper__slider");
      if ($swiperSliders.length) {
        $swiperSliders.each(function () {
          const $element = $(this);
          const options = $element.data('swiper-options');

          try {
            new Swiper($element[0], options);
          } catch (error) {
            console.warn('Swiper initialization failed for element:', $element, error);
          }
        });
      }
    } catch (error) {
      console.warn('Swiper initialization failed:', error);
    }
  }
  try {
    $window.on('load', function () {
      const $preloader = $('.js-preloader');
      if ($preloader.length) {
        $preloader.delay(300).fadeOut(200);
      }
    });
  } catch (error) {
    console.warn('Preloader initialization failed:', error);
  }
  /**
   * Default carousel configurations factory
   * Creates standardized configurations for different carousel types
   */
  const CarouselConfig = {
    /**
     * Main slider configuration
     */
    mainSlider: {
      loop: true,
      animateOut: "fadeOut",
      animateIn: "fadeIn",
      margin: 0,
      nav: true,
      dots: true,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        600: {
          items: 1
        },
        800: {
          items: 1
        },
        992: {
          items: 1
        }
      }
    },

    /**
     * Standard carousel configuration
     */
    standard: {
      loop: true,
      margin: 30,
      nav: false,
      dots: true,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 3
        },
        1320: {
          items: 3
        }
      }
    },
    standardNotDots: {
      loop: true,
      margin: 30,
      nav: false,
      dots: false,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 3
        },
        1320: {
          items: 3
        }
      }
    },

    /**
     * Brand/Gallery carousel configuration
     */
    brand: {
      loop: true,
      margin: 30,
      nav: false,
      dots: false,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 4
        },
        1320: {
          items: 5
        }
      }
    },

    /**
     * Services carousel configuration
     */
    services: {
      loop: true,
      margin: 30,
      nav: false,
      dots: true,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 4
        },
        1320: {
          items: 4
        }
      }
    },

    /**
     * Volunteer carousel configuration
     */
    volunteer: {
      loop: true,
      margin: 30,
      nav: false,
      dots: true,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 4
        },
        1320: {
          items: 4
        }
      }
    },

    /**
     * Testimonial carousel with navigation
     */
    testimonialNav: {
      loop: true,
      margin: 30,
      nav: true,
      dots: false,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 3
        }
      }
    },

    /**
     * Single item carousel configuration
     */
    single: {
      loop: true,
      margin: 30,
      nav: false,
      dots: false,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 1
        },
        992: {
          items: 1
        },
        1200: {
          items: 1
        },
        1320: {
          items: 1
        }
      }
    },

    /**
     * Gallery with minimal margin
     */
    gallery: {
      loop: true,
      margin: 5,
      nav: false,
      dots: false,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 7000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 4
        },
        1320: {
          items: 5
        }
      }
    },

    /**
     * Related products carousel
     */
    relatedProducts: {
      loop: true,
      margin: 30,
      nav: false,
      dots: false,
      smartSpeed: 500,
      autoplay: true,
      autoplayTimeout: 2000,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 2
        },
        992: {
          items: 3
        },
        1200: {
          items: 3
        },
        1320: {
          items: 4
        }
      }
    }
  };

  /**
   * Navigation text configurations
   */
  const NavText = {
    rightArrow: [
      '<span class="icon-right-arrow"></span>',
      '<span class="icon-right-arrow"></span>'
    ],
    rightArrow1: [
      '<span class="icon-right-arrow-1"></span>',
      '<span class="icon-right-arrow-1"></span>'
    ],
    leftRight: [
      '<span class="icon-left-arrow"></span>',
      '<span class="icon-right-arrow"></span>'
    ],
    leftNext: [
      '<span class="icon-left-arrow"></span>',
      '<span class="icon-next"></span>'
    ],
    fontAwesome: [
      '<span class="far fa-long-arrow-left"></span>',
      '<span class="far fa-long-arrow-right"></span>'
    ]
  };

  /**
   * Carousel factory function
   * Creates carousel instances with error handling and configuration merging
   */
  const createCarousel = (selector, baseConfig, customOptions = {}) => {
    try {
      // Check if jQuery and Owl Carousel are available
      if (typeof $ === 'undefined') {
        throw new Error('jQuery is not loaded');
      }

      if (typeof $.fn.owlCarousel === 'undefined') {
        throw new Error('Owl Carousel plugin is not loaded');
      }

      // Cache jQuery selector
      const $element = $(selector);

      if (!$element.length) {
        return false;
      }

      // Merge configurations
      const config = $.extend(true, {}, baseConfig, customOptions);

      // Initialize carousel with error handling
      $element.owlCarousel(config);

      return true;
    } catch (error) {
      console.error(`Error initializing carousel for ${selector}:`, error.message);
      return false;
    }
  };

  /**
   * Initialize main sliders
   */
  const initMainSliders = () => {
    try {
      // Main Slider
      createCarousel('.main-slider__carousel', CarouselConfig.mainSlider, {
        navText: NavText.rightArrow
      });

      // Main Slider Two
      createCarousel('.main-slider-two__carousel', CarouselConfig.mainSlider, {
        navText: NavText.rightArrow1
      });
    } catch (error) {
      console.error('Error initializing main sliders:', error.message);
    }
  };

  /**
   * Initialize donation carousels
   */
  const initDonationCarousels = () => {
    try {
      const donationConfig = $.extend(true, {}, CarouselConfig.single, {
        navText: NavText.rightArrow1
      });

      createCarousel('.donate-one-left__carousel', donationConfig);
      createCarousel('.donate-one-right__carousel', donationConfig);
    } catch (error) {
      console.error('Error initializing donation carousels:', error.message);
    }
  };

  /**
   * Initialize standard content carousels
   */
  const initContentCarousels = () => {
    try {
      const standardConfig = $.extend(true, {}, CarouselConfig.standard, {
        navText: NavText.rightArrow1
      });

      // Initialize multiple carousels with same configuration
      const carouselSelectors = [
        '.blog-carousel-style',
        '.donation-carousel-style',
        '.events-carousel-style',
        '.testimonial-carousel-style'
      ];

      carouselSelectors.forEach(selector => {
        createCarousel(selector, standardConfig);
      });
    } catch (error) {
      console.error('Error initializing content carousels:', error.message);
    }
  };

  /**
   * Initialize brand and gallery carousels
   */
  const initBrandGalleryCarousels = () => {
    try {
      // Brand carousel
      createCarousel('.brand-one__carousel', CarouselConfig.brand, {
        navText: NavText.leftNext
      });

      // Gallery carousel
      createCarousel('.gallery-one__carousel', CarouselConfig.gallery, {
        navText: NavText.leftNext
      });
    } catch (error) {
      console.error('Error initializing brand/gallery carousels:', error.message);
    }
  };
  const initcausesOne = () => {
    try {
      // Brand carousel
      createCarousel('.causes-one__carousel', CarouselConfig.standardNotDots, {
        navText: NavText.leftNext
      });

      // Gallery carousel
      createCarousel('.causes-one__carousel', CarouselConfig.standardNotDots, {
        navText: NavText.leftNext
      });
    } catch (error) {
      console.error('Error initializing brand/causes-one carousels:', error.message);
    }
  };

  /**
   * Initialize service carousels
   */
  const initServiceCarousels = () => {
    try {
      createCarousel('.services-two__carousel', CarouselConfig.services, {
        navText: NavText.rightArrow1
      });

      createCarousel('.volunteer-carousel-style', CarouselConfig.volunteer, {
        navText: NavText.rightArrow1
      });
    } catch (error) {
      console.error('Error initializing service carousels:', error.message);
    }
  };

  /**
   * Initialize testimonial carousels
   */
  const initTestimonialCarousels = () => {
    try {
      // Testimonial Two
      createCarousel('.testimonial-two__carousel', CarouselConfig.testimonialNav, {
        navText: NavText.leftRight
      });

      // Testimonial Three
      createCarousel('.testimonial-three__carousel', $.extend(true, {}, CarouselConfig.testimonialNav, {
        responsive: {
          0: {
            items: 1
          },
          768: {
            items: 2
          },
          992: {
            items: 3
          },
          1200: {
            items: 3
          },
          1320: {
            items: 3
          }
        }
      }), {
        navText: NavText.fontAwesome
      });
    } catch (error) {
      console.error('Error initializing testimonial carousels:', error.message);
    }
  };

  /**
   * Initialize footer carousel
   */
  const initFooterCarousel = () => {
    try {
      createCarousel('.footer-widget__carousel', $.extend(true, {}, CarouselConfig.single, {
        margin: 0,
        navText: NavText.leftNext
      }));
    } catch (error) {
      console.error('Error initializing footer carousel:', error.message);
    }
  };

  /**
   * Initialize related products carousel
   */
  const initRelatedProductsCarousel = () => {
    try {
      createCarousel('.related-products__carousel', CarouselConfig.relatedProducts, {
        navText: NavText.leftRight
      });
    } catch (error) {
      console.error('Error initializing related products carousel:', error.message);
    }
  };

  /**
   * Initialize complex testimonial carousel with thumbnails
   */
  const initComplexTestimonialCarousel = () => {
    try {
      // Check if both elements exist
      const $mainCarousel = $('.testimonial-one__carousel');
      const $thumbCarousel = $('.testimonial-one__thumb-carousel');

      if (!$mainCarousel.length || !$thumbCarousel.length) {
        return false;
      }

      let syncedSecondary = true;

      // Initialize main carousel
      const mainCarousel = $mainCarousel.owlCarousel({
        items: 1,
        slideSpeed: 2000,
        nav: false,
        autoplay: true,
        dots: false,
        loop: true,
        navText: [
          '<i class="icon-left-arrow" aria-hidden="true"></i>',
          '<i class="icon-right-arrow" aria-hidden="true"></i>'
        ]
      }).on("changed.owl.carousel", syncPosition);

      // Initialize thumbnail carousel
      const thumbCarousel = $thumbCarousel
        .on("initialized.owl.carousel", function () {
          $thumbCarousel.find(".owl-item").eq(0).addClass("current");
        })
        .owlCarousel({
          items: 4,
          dots: true,
          nav: false,
          navText: [
            '<i class="icon-left-arrow" aria-hidden="true"></i>',
            '<i class="icon-right-arrow" aria-hidden="true"></i>'
          ],
          smartSpeed: 700,
          slideBy: 4
        })
        .on("changed.owl.carousel", syncPosition2);

      /**
       * Sync main carousel position with thumbnails
       */
      function syncPosition(el) {
        try {
          const count = el.item.count - 1;
          let current = Math.round(el.item.index - el.item.count / 2 - 0.5);

          if (current < 0) {
            current = count;
          }
          if (current > count) {
            current = 0;
          }

          $thumbCarousel
            .find(".owl-item")
            .removeClass("current")
            .eq(current)
            .addClass("current");

          const onscreen = $thumbCarousel.find(".owl-item.active").length - 1;
          const start = $thumbCarousel.find(".owl-item.active").first().index();
          const end = $thumbCarousel.find(".owl-item.active").last().index();

          if (current > end) {
            $thumbCarousel.data("owl.carousel").to(current, 500, true);
          }
          if (current < start) {
            $thumbCarousel.data("owl.carousel").to(current - onscreen, 500, true);
          }
        } catch (error) {
          console.error('Error in syncPosition:', error.message);
        }
      }

      /**
       * Sync thumbnail carousel with main carousel
       */
      function syncPosition2(el) {
        try {
          if (syncedSecondary) {
            const number = el.item.index;
            $mainCarousel.data("owl.carousel").to(number, 500, true);
          }
        } catch (error) {
          console.error('Error in syncPosition2:', error.message);
        }
      }

      // Handle thumbnail clicks
      $thumbCarousel.on("click", ".owl-item", function (e) {
        try {
          e.preventDefault();
          const number = $(this).index();
          $mainCarousel.data("owl.carousel").to(number, 500, true);
        } catch (error) {
          console.error('Error handling thumbnail click:', error.message);
        }
      });

      return true;
    } catch (error) {
      console.error('Error initializing complex testimonial carousel:', error.message);
      return false;
    }
  };

  /*--------------------------------------------------------------
    AOS Animation
  --------------------------------------------------------------*/
  function initAOS() {
    try {
      if (typeof AOS === 'undefined') {
        console.warn('AOS library not loaded');
        return;
      }

      const $aosElements = $("[data-aos]");
      if ($aosElements.length) {
        AOS.init({
          duration: '1200',
          disable: 'false',
          easing: 'ease',
          mirror: true
        });
      }
    } catch (error) {
      console.warn('AOS initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Round Progress Script
  --------------------------------------------------------------*/
  function initRoundProgress() {
    try {
      const $dialElements = $('.dial');
      if ($dialElements.length && typeof jQuery.fn.appear === 'function' && typeof jQuery.fn.knob === 'function') {
        $dialElements.appear(function () {
          const $element = $(this);
          const color = $element.attr('data-fgColor');
          const percentage = $element.attr('value');

          try {
            $element.knob({
              'value': 0,
              'min': 0,
              'max': 100,
              'skin': 'tron',
              'readOnly': true,
              'thickness': 0.15,
              'dynamicDraw': true,
              'displayInput': false
            });

            $({
              value: 0
            }).animate({
              value: percentage
            }, {
              duration: 2000,
              easing: 'swing',
              progress: function () {
                $element.val(Math.ceil(this.value)).trigger('change');
              }
            });
          } catch (error) {
            console.warn('Knob initialization failed for element:', $element, error);
          }
        }, {
          accY: 20
        });
      }
    } catch (error) {
      console.warn('Round Progress initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Vegas Slider
  --------------------------------------------------------------*/
  function initVegasSlider() {
    try {
      if (typeof jQuery.fn.vegas === 'undefined') {
        console.warn('Vegas slider library not loaded');
        return;
      }

      const $sliderElements = $(".slider-bg-slide");
      if ($sliderElements.length) {
        $sliderElements.each(function () {
          const $self = $(this);
          const bgSlideOptions = $self.data("options");

          try {
            $self.vegas(bgSlideOptions);
          } catch (error) {
            console.warn('Vegas slider initialization failed for element:', $self, error);
          }
        });
      }
    } catch (error) {
      console.warn('Vegas slider initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Custom Cursor
  --------------------------------------------------------------*/
  function initCustomCursor() {
    try {
      const $customCursor = $(".custom-cursor");
      if (!$customCursor.length) return;

      const cursor = document.querySelector('.custom-cursor__cursor');
      const cursorInner = document.querySelector('.custom-cursor__cursor-two');
      const links = document.querySelectorAll('a');

      if (!cursor || !cursorInner) {
        console.warn('Custom cursor elements not found');
        return;
      }

      // Mouse move handlers
      const handleMouseMove = throttle((e) => {
        cursor.style.transform = `translate3d(calc(${e.clientX}px - 50%), calc(${e.clientY}px - 50%), 0)`;
        cursorInner.style.left = e.clientX + 'px';
        cursorInner.style.top = e.clientY + 'px';
      }, 16); // ~60fps

      document.addEventListener('mousemove', handleMouseMove);

      // Mouse down/up handlers
      document.addEventListener('mousedown', function () {
        cursor.classList.add('click');
        cursorInner.classList.add('custom-cursor__innerhover');
      });

      document.addEventListener('mouseup', function () {
        cursor.classList.remove('click');
        cursorInner.classList.remove('custom-cursor__innerhover');
      });

      // Link hover handlers
      links.forEach(link => {
        link.addEventListener('mouseover', () => {
          cursor.classList.add('custom-cursor__hover');
        });
        link.addEventListener('mouseleave', () => {
          cursor.classList.remove('custom-cursor__hover');
        });
      });
    } catch (error) {
      console.warn('Custom cursor initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Amount Button Handler
  --------------------------------------------------------------*/
  function initAmountButtons() {
    try {
      $document.on("click", ".amount-btn", function () {
        const $amountButtons = $(".amount-btn");
        const $addAmountValue = $(".addAmount-value");

        $amountButtons.removeClass("active");
        $(this).addClass("active");

        const buttonValue = $(this).text();
        $addAmountValue.val(buttonValue);
      });
    } catch (error) {
      console.warn('Amount buttons initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Progress Count Bar
  --------------------------------------------------------------*/
  function initProgressBars() {
    try {
      if (typeof jQuery.fn.appear === 'undefined') {
        console.warn('Appear library not loaded');
        return;
      }

      // Count bars
      const $countBars = $(".count-bar");
      if ($countBars.length) {
        $countBars.appear(function () {
          const $element = $(this);
          const percent = $element.data("percent");
          $element.css("width", percent).addClass("counted");
        }, {
          accY: -50
        });
      }

      // Progress levels
      const $progressFills = $(".progress-levels .progress-box .bar-fill");
      if ($progressFills.length) {
        $progressFills.appear(function () {
          const progressWidth = $(this).attr("data-percent");
          $(this).css("width", progressWidth + "%");
        }, {
          accY: 0
        });
      }
    } catch (error) {
      console.warn('Progress bars initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Fact Counter
  --------------------------------------------------------------*/
  function initFactCounter() {
    try {
      if (typeof jQuery.fn.appear === 'undefined') {
        console.warn('Appear library not loaded');
        return;
      }

      const $countBoxes = $(".count-box");
      if ($countBoxes.length) {
        $countBoxes.appear(function () {
          const $element = $(this);
          const $countText = $element.find(".count-text");
          const endNumber = $countText.attr("data-stop");
          const speed = parseInt($countText.attr("data-speed"), 10) || 2000;

          if (!$element.hasClass("counted")) {
            $element.addClass("counted");

            try {
              $({
                countNum: $countText.text()
              }).animate({
                countNum: endNumber
              }, {
                duration: speed,
                easing: "linear",
                step: function () {
                  $countText.text(Math.floor(this.countNum));
                },
                complete: function () {
                  $countText.text(this.countNum);
                }
              });
            } catch (animationError) {
              console.warn('Counter animation failed:', animationError);
              $countText.text(endNumber);
            }
          }
        }, {
          accY: 0
        });
      }
    } catch (error) {
      console.warn('Fact counter initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Accordion
  --------------------------------------------------------------*/
  function initAccordion() {
    try {
      const $accordionGroups = $(".accrodion-grp");
      if ($accordionGroups.length) {
        $accordionGroups.each(function () {
          const $self = $(this);
          const accordionName = $self.data("grp-name");
          const $accordions = $self.find(".accrodion");

          $self.addClass(accordionName);
          $self.find(".accrodion .accrodion-content").hide();
          $self.find(".accrodion.active").find(".accrodion-content").show();

          $accordions.each(function () {
            const $accordion = $(this);
            $accordion.find(".accrodion-title").on("click", function () {
              const $title = $(this);
              const $parent = $title.parent();

              if (!$parent.hasClass("active")) {
                $(`.accrodion-grp.${accordionName}`)
                  .find(".accrodion")
                  .removeClass("active")
                  .find(".accrodion-content")
                  .slideUp();

                $parent.addClass("active")
                  .find(".accrodion-content")
                  .slideDown();
              }
            });
          });
        });
      }
    } catch (error) {
      console.warn('Accordion initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Form Validation
  --------------------------------------------------------------*/
  function initFormValidation() {
    try {
      if (typeof jQuery.fn.validate === 'undefined') {
        console.warn('Validation library not loaded');
        return;
      }

      const $forms = $(".contact-form-validated");
      $forms.each(function () {
        const $form = $(this);

        try {
          $form.validate({
            rules: {
              email: {
                required: true,
                email: true
              }
            },
            submitHandler: function (form) {
              const $formElement = $(form);

              $.post(
                $formElement.attr("action"),
                $formElement.serialize(),
                function (response) {
                  $formElement.find(".result").html(response);
                  $formElement.find('input[type="text"], input[type="email"], textarea').val("");
                }
              ).fail(function () {
                console.warn('Form submission failed');
              });

              return false;
            }
          });
        } catch (validationError) {
          console.warn('Form validation setup failed:', validationError);
        }
      });
    } catch (error) {
      console.warn('Form validation initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Magnific Popup
  --------------------------------------------------------------*/
  function initMagnificPopup() {
    try {
      if (typeof jQuery.fn.magnificPopup === 'undefined') {
        console.warn('Magnific Popup library not loaded');
        return;
      }

      // Video popup
      const $videoPopup = $(".video-popup");
      if ($videoPopup.length) {
        $videoPopup.magnificPopup({
          type: "iframe",
          mainClass: "mfp-fade",
          removalDelay: 160,
          preloader: true,
          fixedContentPos: false
        });
      }

      // Image popup
      const $imagePopup = $(".img-popup");
      if ($imagePopup.length) {
        const groups = {};

        $imagePopup.each(function () {
          const id = parseInt($(this).attr("data-group"), 10);
          if (!groups[id]) {
            groups[id] = [];
          }
          groups[id].push(this);
        });

        $.each(groups, function () {
          $(this).magnificPopup({
            type: "image",
            closeOnContentClick: true,
            closeBtnInside: false,
            gallery: {
              enabled: true
            }
          });
        });
      }
    } catch (error) {
      console.warn('Magnific Popup initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Countdown Timer
  --------------------------------------------------------------*/
  function initCountdown() {
    try {
      if (typeof jQuery.fn.countdown === 'undefined') {
        console.warn('Countdown library not loaded');
        return;
      }
      //=== CountDownTimer===
      if ($('.coming-soon-countdown').length) {
        $('.coming-soon-countdown').each(function () {
          const Self = $(this);
          const countDate = Self.data('countdown-time'); // getting date

          Self.countdown(countDate, function (event) {
            $(this).html('<li> <div class="box"> <span class="days">' + event.strftime('%D') + '</span> <span class="timeRef">days</span> </div> </li> <li> <div class="box"> <span class="hours">' + event.strftime('%H') + '</span> <span class="timeRef clr-1">hrs</span> </div> </li> <li> <div class="box"> <span class="minutes">' + event.strftime('%M') + '</span> <span class="timeRef clr-2">mins</span> </div> </li> <li> <div class="box"> <span class="seconds">' + event.strftime('%S') + '</span> <span class="timeRef clr-3">secs</span> </div> </li>');
          });
        });
      };
    } catch (error) {
      console.warn('Countdown initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Dynamic Menu Class
  --------------------------------------------------------------*/
  function setDynamicCurrentMenuClass(selector) {
    try {
      const fileName = window.location.href.split("/").reverse()[0];

      selector.find("li").each(function () {
        const $listItem = $(this);
        const $anchor = $listItem.find("a");

        if ($anchor.attr("href") === fileName) {
          $listItem.addClass("current");
        }
      });

      // Add current class to parent li if child has current
      selector.children("li").each(function () {
        const $listItem = $(this);
        if ($listItem.find(".current").length) {
          $listItem.addClass("current");
        }
      });

      // If no filename, set first item as current
      if (!fileName) {
        selector.find("li").eq(0).addClass("current");
      }
    } catch (error) {
      console.warn('Dynamic menu class setting failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Navigation Setup
  --------------------------------------------------------------*/
  function initNavigation() {
    try {
        
    //   const $mainMenuList = $(".main-menu__list");
    //   if ($mainMenuList.length) {
    //     setDynamicCurrentMenuClass($mainMenuList);
    //   }

      // Mobile navigation
      const $mainMenuList = $(".main-menu__list");
      const $mobileNavContainer = $(".mobile-nav__container");
      if ($mainMenuList.length && $mobileNavContainer.length) {
        const navContent = document.querySelector(".main-menu__list");
        if (navContent) {
          $mobileNavContainer.html(navContent.outerHTML);
        }
      }

      // Mobile dropdown toggles
      const $mobileDropdowns = $(".mobile-nav__container .main-menu__list .dropdown > a");
      if ($mobileDropdowns.length) {
        $mobileDropdowns.each(function () {
          const $self = $(this);
          const toggleBtn = document.createElement("BUTTON");

          toggleBtn.setAttribute("aria-label", "dropdown toggler");
          toggleBtn.innerHTML = "<i class='fa fa-angle-down'></i>";

          $self.append(toggleBtn);

          $self.find("button").on("click", function (e) {
            e.preventDefault();
            const $button = $(this);

            $button.toggleClass("expanded");
            $button.parent().toggleClass("expanded");
            $button.parent().parent().children("ul").slideToggle();
          });
        });
      }

      // Mobile nav toggler
      const $mobileNavToggler = $(".mobile-nav__toggler");
      const $mobileNavWrapper = $(".mobile-nav__wrapper");

      if ($mobileNavToggler.length) {
        $mobileNavToggler.on("click", function (e) {
          e.preventDefault();
          $mobileNavWrapper.toggleClass("expanded");
          $body.toggleClass("locked");
        });
      }
    } catch (error) {
      console.warn('Navigation initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Header Search
  --------------------------------------------------------------*/
  function initHeaderSearch() {
    try {
      const $searchTogglerBox = $('.searcher-toggler-box');
      const $closeSearch = $('.close-search');
      const $searchColorLayer = $('.search-popup .color-layer');

      if ($searchTogglerBox.length) {
        $searchTogglerBox.on('click', function () {
          $body.addClass('search-active');
        });

        $closeSearch.on('click', function () {
          $body.removeClass('search-active');
        });

        $searchColorLayer.on('click', function () {
          $body.removeClass('search-active');
        });
      }
    } catch (error) {
      console.warn('Header search initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Odometer
  --------------------------------------------------------------*/
  function initOdometer() {
    try {
      if (typeof Odometer === 'undefined') {
        console.warn('Odometer library not loaded');
        return;
      }

      const $odometerElements = $(".odometer");
      if ($odometerElements.length && typeof jQuery.fn.appear === 'function') {
        $odometerElements.each(function () {
          const $element = $(this);

          $element.appear(function () {
            const countNumber = $element.attr("data-count");
            $element.html(countNumber);
          });
        });
      }
    } catch (error) {
      console.warn('Odometer initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Dynamic Year
  --------------------------------------------------------------*/
  function initDynamicYear() {
    try {
      const $dynamicYearElements = $(".dynamic-year");
      if ($dynamicYearElements.length) {
        const currentYear = new Date().getFullYear();
        $dynamicYearElements.html(currentYear);
      }
    } catch (error) {
      console.warn('Dynamic year initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    WOW Animation
  --------------------------------------------------------------*/
  function initWOW() {
    try {
      if (typeof WOW === 'undefined') {
        console.warn('WOW library not loaded');
        return;
      }

      const $wowElements = $(".wow");
      if ($wowElements.length) {
        const wow = new WOW({
          boxClass: "wow",
          animateClass: "animated",
          mobile: true,
          live: true
        });
        wow.init();
      }
    } catch (error) {
      console.warn('WOW animation initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Tabs
  --------------------------------------------------------------*/
  function initTabs() {
    try {
      const $tabsBoxes = $(".tabs-box");
      if ($tabsBoxes.length) {
        $tabsBoxes.find(".tab-buttons .tab-btn").on("click", function (e) {
          e.preventDefault();

          const $tabButton = $(this);
          const target = $tabButton.attr("data-tab");
          const $target = $(target);

          if ($target.is(":visible")) {
            return false;
          }

          const $tabsBox = $target.parents(".tabs-box");

          // Remove active class from all buttons
          $tabsBox.find(".tab-buttons .tab-btn").removeClass("active-btn");
          $tabButton.addClass("active-btn");

          // Hide all tabs and show target
          $tabsBox.find(".tabs-content .tab")
            .fadeOut(0)
            .removeClass("active-tab");

          $target.fadeIn(300).addClass("active-tab");
        });
      }
    } catch (error) {
      console.warn('Tabs initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Portfolio Masonry
  --------------------------------------------------------------*/
  function initPortfolioMasonry() {
    try {
      if (typeof jQuery.fn.isotope === 'undefined') {
        console.warn('Isotope library not loaded');
        return;
      }

      // Masonry layout
      const $masonaryLayout = $(".masonary-layout");
      if ($masonaryLayout.length) {
        $masonaryLayout.isotope({
          layoutMode: "masonry"
        });
      }

      // Post filter
      const $postFilter = $(".post-filter");
      if ($postFilter.length) {
        $postFilter.find("li .filter-text").on("click", function () {
          const $filterText = $(this);
          const $parent = $filterText.parent();
          const selector = $parent.attr("data-filter");

          $postFilter.find("li").removeClass("active");
          $parent.addClass("active");

          const $filterLayout = $(".filter-layout");
          if ($filterLayout.length) {
            $filterLayout.isotope({
              filter: selector,
              animationOptions: {
                duration: 500,
                easing: "linear",
                queue: false
              }
            });
          }

          return false;
        });
      }

      // Dynamic filter counter
      const $dynamicFilterCounter = $(".post-filter.has-dynamic-filters-counter");
      if ($dynamicFilterCounter.length) {
        const $activeFilterItems = $dynamicFilterCounter.find("li");
        const $filterLayout = $(".filter-layout");

        $activeFilterItems.each(function () {
          const $item = $(this);
          const filterElement = $item.data("filter");
          const count = $filterLayout.find(filterElement).length;

          $item.children(".filter-text")
            .append(`<span class="count">${count}</span>`);
        });
      }
    } catch (error) {
      console.warn('Portfolio masonry initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Smooth Scroll
  --------------------------------------------------------------*/
  function initSmoothScroll() {
    try {
      const $scrollToLinks = $(".scrollToLink");
      if ($scrollToLinks.length) {
        $scrollToLinks.children("a").on("click", function (event) {
          event.preventDefault();

          const $target = $(this);
          const targetHref = $target.attr("href");
          const $targetElement = $(targetHref);

          if (!$targetElement.length) return;

          const headerHeight = $window.scrollTop() > 10 ? 90 : 90;
          const targetOffset = $targetElement.offset().top - headerHeight;

          $("html, body").stop().animate({
            scrollTop: targetOffset
          }, 200, "easeInOutExpo");

          $scrollToLinks.removeClass("current current-menu-ancestor current_page_item current-menu-parent");
          $target.parent().addClass("current");
        });
      }
    } catch (error) {
      console.warn('Smooth scroll initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    One Page Menu Scroll
  --------------------------------------------------------------*/
  function handleOnePageMenuScroll() {
    try {
      const windscroll = $window.scrollTop();
      const $onePageMenu = $(".one-page-scroll-menu");

      if (!$onePageMenu.length) return;

      if (windscroll >= 117) {
        const $menuAnchors = $onePageMenu.find(".scrollToLink").children("a");

        $menuAnchors.each(function () {
          const $anchor = $(this);
          const sections = $anchor.attr("href");
          const $section = $(sections);

          if ($section.length && $section.offset().top <= windscroll + 100) {
            const sectionId = $section.attr("id");

            $onePageMenu.find("li").removeClass("current current-menu-ancestor current_page_item current-menu-parent");
            $onePageMenu.find(`a[href*=\\#${sectionId}]`).parent().addClass("current");
          }
        });
      } else {
        $onePageMenu.find("li.current").removeClass("current");
        $onePageMenu.find("li:first").addClass("current");
      }
    } catch (error) {
      console.warn('One page menu scroll handling failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Scrollbar Progress
  --------------------------------------------------------------*/
  function handleScrollbarProgress() {
    try {
      const $scrollToTop = $(".scroll-to-top");
      if (!$scrollToTop.length) return;

      const bodyHeight = $body.height();
      const scrollPos = $window.innerHeight() + $window.scrollTop();
      let percentage = (scrollPos / bodyHeight) * 100;

      if (percentage > 100) {
        percentage = 100;
      }

      $scrollToTop.find(".scroll-to-top__inner").css("width", percentage + "%");
    } catch (error) {
      console.warn('Scrollbar progress handling failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Title Animation with GSAP
  --------------------------------------------------------------*/
  function initTitleAnimation() {
    try {
      if (typeof gsap === 'undefined' || typeof SplitText === 'undefined') {
        console.warn('GSAP or SplitText not loaded');
        return;
      }

      const $titleAnimationElements = $('.sec-title-animation');
      if (!$titleAnimationElements.length) return;

      const quotes = document.querySelectorAll(".sec-title-animation .title-animation");

      quotes.forEach(quote => {
        try {
          // Reset if needed
          if (quote.animation) {
            quote.animation.progress(1).kill();
            quote.split.revert();
          }

          const classNames = quote.closest('.sec-title-animation').className;
          const animation = classNames.split('animation-');

          if (animation[1] === "style4") return;

          quote.split = new SplitText(quote, {
            type: "lines,words,chars",
            linesClass: "split-line"
          });

          gsap.set(quote, {
            perspective: 400
          });

          let animationSettings = {};

          if (animation[1] === "style1") {
            gsap.set(quote.split.chars, {
              opacity: 0,
              y: "90%",
              rotateX: "-40deg"
            });
            animationSettings = {
              x: "0",
              y: "0",
              rotateX: "0",
              opacity: 1
            };
          } else if (animation[1] === "style2") {
            gsap.set(quote.split.chars, {
              opacity: 0,
              x: "50"
            });
            animationSettings = {
              x: "0",
              opacity: 1
            };
          } else if (animation[1] === "style3") {
            gsap.set(quote.split.chars, {
              opacity: 0,
            });
            animationSettings = {
              opacity: 1
            };
          }

          quote.animation = gsap.to(quote.split.chars, {
            scrollTrigger: {
              trigger: quote,
              start: "top 90%",
            },
            ...animationSettings,
            duration: 1,
            ease: "back.out(1.7)",
            stagger: 0.02
          });
        } catch (animationError) {
          console.warn('Title animation failed for element:', quote, animationError);
        }
      });

      if (typeof ScrollTrigger !== 'undefined') {
        ScrollTrigger.addEventListener("refresh", initTitleAnimation);
      }
    } catch (error) {
      console.warn('Title animation initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Price Filter
  --------------------------------------------------------------*/
  function initPriceFilter() {
    try {
      if (typeof jQuery.fn.slider === 'undefined') {
        console.warn('jQuery UI slider not loaded');
        return;
      }

      const $priceRanger = $(".price-ranger");
      if ($priceRanger.length) {
        const $sliderRange = $priceRanger.find("#slider-range");
        const $minInput = $priceRanger.find(".ranger-min-max-block .min");
        const $maxInput = $priceRanger.find(".ranger-min-max-block .max");

        if ($sliderRange.length) {
          $sliderRange.slider({
            range: true,
            min: 0,
            max: 5000,
            values: [0, 3000],
            slide: function (event, ui) {
              $minInput.val(ui.values[0]);
              $maxInput.val(ui.values[1]);
            },
          });

          $minInput.val($sliderRange.slider("values", 0));
          $maxInput.val($sliderRange.slider("values", 1));
        }
      }
    } catch (error) {
      console.warn('Price filter initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Quantity Controls
  --------------------------------------------------------------*/
  function initQuantityControls() {
    try {
      const $addButtons = $(".add");
      const $subButtons = $(".sub");

      $addButtons.on("click", function () {
        const $input = $(this).prev();
        const currentValue = parseInt($input.val()) || 0;

        if (currentValue < 999) {
          $input.val(currentValue + 1);
        }
      });

      $subButtons.on("click", function () {
        const $input = $(this).next();
        const currentValue = parseInt($input.val()) || 0;

        if (currentValue > 1) {
          $input.val(currentValue - 1);
        }
      });
    } catch (error) {
      console.warn('Quantity controls initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Checkout Payment
  --------------------------------------------------------------*/
  function initCheckoutPayment() {
    try {
      const $checkoutPaymentTitles = $(".checkout__payment__title");
      if ($checkoutPaymentTitles.length) {
        // Hide all content initially
        $(".checkout__payment__item").find(".checkout__payment__content").hide();
        $(".checkout__payment__item--active").find(".checkout__payment__content").show();

        $checkoutPaymentTitles.on("click", function (e) {
          e.preventDefault();

          const $title = $(this);
          const $paymentContainer = $title.parents(".checkout__payment");
          const $currentItem = $title.parent();

          // Remove active class from all items
          $paymentContainer.find(".checkout__payment__item")
            .removeClass("checkout__payment__item--active");
          $paymentContainer.find(".checkout__payment__content").slideUp();

          // Add active class to current item
          $currentItem.addClass("checkout__payment__item--active");
          $currentItem.find(".checkout__payment__content").slideDown();
        });
      }
    } catch (error) {
      console.warn('Checkout payment initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Product All Tab
  --------------------------------------------------------------*/
  function initProductTabs() {
    try {
      const $productTabs = $(".product__all-tab");
      if ($productTabs.length) {
        $productTabs.find(".tabs-button-box .tab-btn-item").on("click", function (e) {
          e.preventDefault();

          const $button = $(this);
          const target = $button.attr("data-tab");
          const $target = $(target);

          if ($target.hasClass("active-tab")) {
            return false;
          }

          // Remove active classes
          $productTabs.find(".tabs-button-box .tab-btn-item")
            .removeClass("active-btn-item");
          $productTabs.find(".tabs-content-box .tab-content-box-item")
            .removeClass("tab-content-box-item-active");

          // Add active classes
          $button.addClass("active-btn-item");
          $target.addClass("tab-content-box-item-active");
        });
      }
    } catch (error) {
      console.warn('Product tabs initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Shop Details Swiper
  --------------------------------------------------------------*/
  function initShopDetailsSwiper() {
    try {
      if (typeof Swiper === 'undefined') {
        console.warn('Swiper library not loaded');
        return;
      }

      const thumbElement = document.getElementById("shop-details-one__thumb");
      const carouselElement = document.getElementById("shop-details-one__carousel");

      if (thumbElement && carouselElement) {
        const testimonialsThumb = new Swiper("#shop-details-one__thumb", {
          slidesPerView: 3,
          spaceBetween: 0,
          speed: 1400,
          watchSlidesVisibility: true,
          watchSlidesProgress: true,
          loop: true,
          autoplay: {
            delay: 5000
          }
        });

        const testimonialsCarousel = new Swiper("#shop-details-one__carousel", {
          observer: true,
          observeParents: true,
          loop: true,
          speed: 1400,
          mousewheel: false,
          slidesPerView: 1,
          autoplay: {
            delay: 5000
          },
          thumbs: {
            swiper: testimonialsThumb
          },
          pagination: {
            el: '#testimonials-one__carousel-pagination',
            type: 'bullets',
            clickable: true
          },
          navigation: {
            nextEl: "#product-details__swiper-button-next",
            prevEl: "#product-details__swiper-button-prev"
          },
        });
      }
    } catch (error) {
      console.warn('Shop details swiper initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Marquee Mode
  --------------------------------------------------------------*/
  function initMarquee() {
    try {
      if ($(".marquee_mode").length) {
        $('.marquee_mode').marquee({
          speed: 50,
          gap: 0,
          delayBeforeStart: 0,
          direction: 'left',
          duplicated: true,
          pauseOnHover: true,
          startVisible: true,
        });
      }

      if ($(".marquee_mode-two").length) {
        $('.marquee_mode-two').marquee({
          speed: 50,
          gap: 0,
          delayBeforeStart: 0,
          direction: 'right',
          duplicated: true,
          pauseOnHover: true,
          startVisible: true,
        });
      }

      if ($(".marquee_mode-1").length) {
        $('.marquee_mode-1').marquee({
          speed: 40,
          gap: 20,
          delayBeforeStart: 0,
          direction: 'left',
          duplicated: true,
          pauseOnHover: true,
          startVisible: true,
        });
      }
    } catch (error) {
      console.warn('Marquee initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Nice Select
  --------------------------------------------------------------*/
  function initNiceSelect() {
    try {
      if (typeof jQuery.fn.niceSelect === 'undefined') {
        console.warn('Nice Select library not loaded');
        return;
      }

      const $selectElements = $('select:not(.ignore)');
      if ($selectElements.length) {
        $selectElements.niceSelect();
      }
    } catch (error) {
      console.warn('Nice Select initialization failed:', error);
    }
  }


  /*--------------------------------------------------------------
    Sticky Header Handler
  --------------------------------------------------------------*/
  
 
    // Sticky header
  if ($(".sticky-header__content").length) {
    let navContent = $(".main-menu").html();
    let mobileNavContainer = $(".sticky-header__content");
    mobileNavContainer.html(navContent);
  }

  
  const handleStickyHeader = throttle(function () {
    try {
      const $strickedMenu = $(".stricked-menu");
      const $stickyHeaderOnePage = $(".sticky-header--one-page");
      const headerScrollPos = 300;
      const onePageScrollPos = 130;
      const scrollTop = $window.scrollTop();

      // Regular sticky header
      if ($strickedMenu.length) {
        if (scrollTop > headerScrollPos) {
          $strickedMenu.addClass("stricky-fixed");
        } else {
          $strickedMenu.removeClass("stricky-fixed");
        }
      }

      // One page sticky header
      if ($stickyHeaderOnePage.length) {
        if (scrollTop > onePageScrollPos) {
          $stickyHeaderOnePage.addClass("active");
        } else {
          $stickyHeaderOnePage.removeClass("active");
        }
      }
    } catch (error) {
      console.warn('Sticky header handling failed:', error);
    }
  }, 16);

  /*--------------------------------------------------------------
    Scroll to Top Handler
  --------------------------------------------------------------*/
  const handleScrollToTop = throttle(function () {
    try {
      const $scrollToTopBtn = $(".scroll-to-top");
      if ($scrollToTopBtn.length) {
        const scrollTop = $window.scrollTop();

        if (scrollTop > 500) {
          $scrollToTopBtn.addClass("show");
        } else {
          $scrollToTopBtn.removeClass("show");
        }
      }
    } catch (error) {
      console.warn('Scroll to top handling failed:', error);
    }
  }, 16);

  /*--------------------------------------------------------------
    Combined Scroll Handler
  --------------------------------------------------------------*/
  const handleScroll = throttle(function () {
    handleStickyHeader();
    handleScrollToTop();
    handleScrollbarProgress();
    handleOnePageMenuScroll();
  }, 16);

  /*--------------------------------------------------------------
    Window Resize Handler
  --------------------------------------------------------------*/
  const handleResize = debounce(function () {
    try {
      // Refresh ScrollTrigger if available
      if (typeof ScrollTrigger !== 'undefined') {
        ScrollTrigger.refresh();
      }
    } catch (error) {
      console.warn('Resize handling failed:', error);
    }
  }, 250);
  try {
    // Post filter functionality
    const $postFilter = $(".post-filter");
    if ($postFilter.length && typeof jQuery.fn.isotope !== 'undefined') {
      const $filterLayout = $(".filter-layout");

      // Initialize isotope
      $filterLayout.isotope({
        filter: ".filter-item",
        animationOptions: {
          duration: 500,
          easing: "linear",
          queue: false
        }
      });

      // Filter click handlers
      $postFilter.find("li").on("click", function () {
        const $self = $(this);
        const selector = $self.attr("data-filter");

        $postFilter.find("li").removeClass("active");
        $self.addClass("active");

        $filterLayout.isotope({
          filter: selector,
          animationOptions: {
            duration: 500,
            easing: "linear",
            queue: false
          }
        });

        return false;
      });
    }

    // Dynamic filter counter
    const $dynamicFilterCounter = $(".post-filter.has-dynamic-filter-counter");
    if ($dynamicFilterCounter.length) {
      const $activeFilterItems = $dynamicFilterCounter.find("li");
      const $filterLayout = $(".filter-layout");

      $activeFilterItems.each(function () {
        const $item = $(this);
        const filterElement = $item.data("filter");
        const count = $filterLayout.find(filterElement).length;
        $item.append(`<sup>[${count}]</sup>`);
      });
    }
  } catch (error) {
    console.warn('Window load handling failed:', error);
  }
  /*--------------------------------------------------------------
    Initialize All Functions
  --------------------------------------------------------------*/
  function initializeAll() {
    try {

      // Core functionality
      initCustomCursor();
      initNavigation();
      initHeaderSearch();
      initTabs();
      initAccordion();

      // Animations and Effects
      initRoundProgress();
      initProgressBars();
      initFactCounter();
      initVegasSlider();
      initWOW();
      initOdometer();

      // Forms and Interactions
      initFormValidation();
      initAmountButtons();
      initQuantityControls();
      initCheckoutPayment();
      initProductTabs();

      // Media and Popups
      initMagnificPopup();


      // Utility
      initDynamicYear();
      initSmoothScroll();
      initPriceFilter();
      initNiceSelect();

      //Orchestrates all carousel initializations
      // Initialize all carousel types
      initMainSliders();
      initDonationCarousels();
      initContentCarousels();
      initBrandGalleryCarousels();
      initcausesOne();
      initServiceCarousels();
      initTestimonialCarousels();
      initFooterCarousel();
      initRelatedProductsCarousel();
      initComplexTestimonialCarousel();
      initAOS();
      fullHeight();
      initMarquee();
      initCountdown();
    } catch (error) {
      console.warn('Initialization failed:', error);
    }
  }

  /*--------------------------------------------------------------
    Window Load Handler
  --------------------------------------------------------------*/

  $document.ready(function () {
    try {
      initializeAll();
      // Bind event handlers
      $window.on('scroll', handleScroll);
      $window.on('resize', handleResize);
    } catch (error) {
      console.error('Theme initialization failed:', error);
    }

    //new window load event | not use cache $window  cause time issu
    $(window).on("load", function () {
      // UI Components
      initializeGSAP();
      initSwiper();
      initPortfolioMasonry();
      initTitleAnimation();
      initSwiper();
      initPriceFilter();
      initShopDetailsSwiper();


    });

  });
})(jQuery);
