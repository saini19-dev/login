jQuery(document).ready(function($) {
    // Your code here
    $('.slider-container').slick({
        dots: true,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 3000,
        arrows: true
    });

    $('.center-slider').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        centerMode: true,
        arrows: true,
        dots: false,
        speed: 300,
        centerPadding: '80px',
        infinite: true,
        autoplaySpeed: 5000,
        autoplay: false
      });

});
