$(window).on('load', function () {

	$(".mask").click(function(){
	 $(this).setCursorPosition(3);
	}).mask("+7(999) 999-9999");
	

$('.work_slider').slick({
  slidesToShow: 3,
  slidesToScroll: 3,
  variableWidth: true,
  arrows: true,
  dots: true,
  mobileFirst: true,
  prevArrow: '<button type="button" class="slick-prev"></button>',
  nextArrow: '<button type="button" class="slick-next"></button>',
  responsive: [
   {
     breakpoint: 0,
     settings: {
      slidesToShow: 1,
      slidesToScroll: 1,
      dots: false,
      variableWidth: false
     }
   },
   {
     breakpoint: 575,
     settings: {
      slidesToShow: 3,
      slidesToScroll: 3,
      dots: true,
      variableWidth: true
     }
   }
 ]
});
let slickVar  = {
 dots: false,
 arrows: false,
 infinite: true,
 speed: 300,
 slidesToShow: 1,
 slidesToScroll: 1,
 mobileFirst: true,
 arrows: true,
 prevArrow: '<button type="button" class="slick-prev"></button>',
 nextArrow: '<button type="button" class="slick-next"></button>',
 responsive: [
   {
     breakpoint: 575,
     settings: {
      slidesToShow: 2,
      slidesToScroll: 1,
      dots: false,
      arrows: false
     }
   },
   {
     breakpoint: 1023,
     settings: 'unslick'
   }
 ]
};
function runSlick() {
 if( !$('.work_wrap').hasClass('work_slider') ){
  $('.work_wrap').slick(slickVar);
 }
 $('.univer_wrap').slick(slickVar);



 $('.team_box').slick(
  {
    slidesToShow: 3,
    slidesToScroll: 1,
    // centerMode: true,
    variableWidth: true,
    arrows: true,
    dots: true,
    prevArrow: '<button type="button" class="slick-prev"></button>',
    nextArrow: '<button type="button" class="slick-next"></button>',
    responsive: [
     {
       breakpoint: 575,
       settings: {
         dots: false,
         slidesToShow: 1,
         slidesToScroll: 1,
         variableWidth: false,
         centerPadding: '10px'
       }
     }
   ]
  }
 );
 
 
 $('.artic_box').slick({
  slidesToShow: 3,
  slidesToScroll: 3,
  // centerMode: true,
  variableWidth: true,
  arrows: true,
  dots: true,
  prevArrow: '<button type="button" class="slick-prev"></button>',
  nextArrow: '<button type="button" class="slick-next"></button>',
  responsive: [
   {
     breakpoint: 575,
     settings: {
       dots: false,
       slidesToShow: 1,
	   slidesToScroll: 1,
       variableWidth: false,
       centerPadding: '10px'
     }
   }
 ]
});
};
runSlick();
 /*$(window).on('resize', function(){
   var width = $(window).width();
   if(575 > width ) {
     runSlick();
   }
 });*/
	

	
	$("body").append('<noscript><div><img src="https://mc.yandex.ru/watch/87038551" style="position:absolute; left:-9999px;" alt="" /></div></noscript>');
	
	(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(87038551, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
	
  $("body").append('<script async src="https://www.googletagmanager.com/gtag/js?id=G-DTFGJYJ84B"></script>');
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-DTFGJYJ84B');
	
});


$(document).ready(function(){
	
	
	
	
	cackle_widget = window.cackle_widget || [];
			cackle_widget.push({widget: 'Comment', id: 78592});
			(function() {
				var mc = document.createElement('script');
				mc.type = 'text/javascript';
				mc.async = true;
				mc.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cackle.me/widget.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(mc, s.nextSibling);
			})();
$(window).scroll(function(){
		if(!$(".scroll_content").hasClass("loaded")){
			var loaded = $(".scroll_content").addClass("loaded").load("https://sessiusdal.ru/wp-content/themes/session/scroll_content.php", function() {
			
				cackle_widget = window.cackle_widget || [];
			cackle_widget.push({widget: 'Comment', id: 78592});
			(function() {
				var mc = document.createElement('script');
				mc.type = 'text/javascript';
				mc.async = true;
				mc.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cackle.me/widget.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(mc, s.nextSibling);
			})();
	$('.univer_wrap').slick({
 dots: false,
 arrows: false,
 infinite: true,
 speed: 300,
 slidesToShow: 1,
 slidesToScroll: 1,
 mobileFirst: true,
 arrows: true,
 prevArrow: '<button type="button" class="slick-prev"></button>',
 nextArrow: '<button type="button" class="slick-next"></button>',
 responsive: [
   {
     breakpoint: 575,
     settings: {
      slidesToShow: 2,
      slidesToScroll: 1,
      dots: false,
      arrows: false
     }
   },
   {
     breakpoint: 1023,
     settings: 'unslick'
   }
 ]
});
				
$('.team_box').slick({
 dots: false,
 arrows: false,
 infinite: true,
 speed: 300,
 slidesToShow: 1,
 slidesToScroll: 1,
 mobileFirst: true,
 arrows: true,
 prevArrow: '<button type="button" class="slick-prev"></button>',
 nextArrow: '<button type="button" class="slick-next"></button>',
 responsive: [
   {
     breakpoint: 575,
     settings: {
      slidesToShow: 2,
      slidesToScroll: 1,
      dots: false,
      arrows: false
     }
   },
   {
     breakpoint: 1023,
     settings: 'unslick'
   }
 ]
});
				
	$('.artic_box').slick({
	  slidesToShow: 3,
	  slidesToScroll: 3,
	  // centerMode: true,
	  variableWidth: true,
	  arrows: true,
	  dots: true,
	  prevArrow: '<button type="button" class="slick-prev"></button>',
	  nextArrow: '<button type="button" class="slick-next"></button>',
	  responsive: [
	   {
		 breakpoint: 575,
		 settings: {
		   dots: false,
		   slidesToShow: 1,
		   slidesToScroll: 1,
		   variableWidth: false,
		   centerPadding: '10px'
		 }
	   }
	 ]
	});

			//runSlick();

			document.querySelectorAll(".wpcf7 > form").forEach((
                function(e){
                    return wpcf7.init(e)
                }
            )
        	);
});
			
			
			
			
		}
	
	});
	
 	
 if (screen.width < 375) {
      var mvp = document.getElementById('vp');
      mvp.setAttribute('content','user-scalable=no,width=375');
  }
 document.addEventListener( 'wpcf7mailsent', function( event ) {
   $("html").addClass('no_scroll');
   $(".popup").addClass('show').find(".wpcf7").remove();
   $(".popup_title").html("СПАСИБО!");
   $(".popup_form span").html("Ваша заявка отправлена. <br>В ближайшее время с Вами свяжутся");
   
   setTimeout(function(){
       $("html").removeClass('no_scroll');
       $(".popup").removeClass('show');
   },2000);
 }, false );
 $.fn.setCursorPosition = function(pos) {
  if ($(this).get(0).setSelectionRange) {
    $(this).get(0).setSelectionRange(pos, pos);
  } else if ($(this).get(0).createTextRange) {
    var range = $(this).get(0).createTextRange();
    range.collapse(true);
    range.moveEnd('character', pos);
    range.moveStart('character', pos);
    range.select();
  }
};
 
	$("body").on("click","a.show_more",function(){
		$(this).toggleClass("active");
  $(this).prev().toggleClass("show_all");
		if( $(this).hasClass("active") ){
			$(this).children('span').html("СКРЫТЬ");
		}else{
			$(this).children('span').html("ПОКАЗАТЬ ЕЩЕ");
		}
		return false;
	});
  $(".top_line li, footer nav li").on("click","a[href^='#']", function (event) {
    //отменяем стандартную обработку нажатия по ссылке
    event.preventDefault();
    //забираем идентификатор бока с атрибута href
    var id  = $(this).attr('href'),
    //узнаем высоту от начала страницы до блока на который ссылается якорь
    top = $(id).offset().top - 120;
    //анимируем переход на расстояние - top за 1500 мс
    $('body,html').animate({scrollTop: top}, 600);
    $(".top_line").removeClass("active");
    $(".top_line li").removeClass("active");
    $("footer nav li").removeClass("active");
    $(this).parent().addClass("active");
  });

  $(window).on('scroll', function () {
   var scrollTop = $(this).scrollTop();
   if(scrollTop > 0){
    $(".top_line").addClass("scroll");
   }else{
    $(".top_line").removeClass("scroll");
   }
   if(scrollTop > 300){
    $(".scroll_top").addClass("show");
   }else{
    $(".scroll_top").removeClass("show");
   }
  });
	
  var scrollTop = $(this).scrollTop();
  if(scrollTop > 0){
   $(".top_line").addClass("scroll");
  }else{
   $(".top_line").removeClass("scroll");
  }
  
  $(".scroll_top").click(function(){
    $('body,html').animate({scrollTop: 0}, 600);
  });
 
  $(".close, .popup_overlay").click(function(){
    $(".popup").removeClass("show");
    $("html").removeClass("no_scroll");
  });
  $(".order_button").click(function(){
	var popup = $(this).attr('href');
    $(".popup").addClass("show");
	$(".popup_form").removeClass("show");
	$(popup).addClass('show');
    $("html").addClass("no_scroll");
	  return false;
  });
  $(".nav_burger").click(function(){
    $(this).closest(".top_line").toggleClass("active");
  });

});



