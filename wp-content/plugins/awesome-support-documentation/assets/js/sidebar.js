jQuery.noConflict();

jQuery(document).ready(function ($) {
  $(".toctree-l1").click(function() {
    $(this).find(".toctree-l1-chapter").toggleClass("toctree-toggled");
    $(this).find(".toctree-l1-toggle").slideToggle( "slow" );
  });

  var url = window.location.href;
	var $current = $('.toctree-l1-toggle li a[href="' + url + '"]');
	$current.parents('.toctree-l1-toggle').slideToggle();
	$current.parents('.toctree-l1').find(".toctree-l1-chapter").toggleClass("toctree-toggled");
	$current.next('.toctree-l1-toggle').slideToggle();

  var $chapter = $(".toctree-toggled");
  $(".wpas-docs-breadcrumbs-menu").text($chapter.text());
  $(".wpas-docs-breadcrumbs-product").text($chapter.closest('div').prev().text());
});

jQuery(document).ready(function ($){
  var m = $(".mobile-nav-toggle");
  m.addClass('fa-bars');
  m.on('click', function(){
    if (m.hasClass('fa-bars')) {
      m.removeClass('fa-bars').addClass('fa-times');
    } else {
      m.removeClass('fa-times').addClass('fa-bars');
    }
  });

  $(".mobile-nav-toggle").click(function(){
    $(".wy-nav-side").toggleClass("shift");
    $(".wy-nav-content-wrap").toggleClass("shift");

  });
});
