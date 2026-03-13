$(window).on('scroll', function () {
  if ($(this).scrollTop() > 20) {
    $('header').addClass('header-scroll');
  } else {
    $('header').removeClass('header-scroll');
  }
});

$('.js-banner-slider').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: true,
  dots: true,
  autoplay: true,
  autoplaySpeed: 4000,
  speed: 800,
  fade: true,
  cssEase: 'ease-in-out'
});

$('.cat-card').wrap('<div class="cat-card-wrap"></div>');
$('#catTrack').slick({
  slidesToShow: 5,
  slidesToScroll: 1,
  prevArrow: $('.car-arrow-prev'),
  nextArrow: $('.car-arrow-next'),

  responsive: [
    {
      breakpoint: 1200,
      settings: {
        slidesToShow: 4
      }
    },
    {
      breakpoint: 992,
      settings: {
        slidesToShow: 3
      }
    },
    {
      breakpoint: 768,
      settings: {
        slidesToShow: 2
      }
    }
  ]
});