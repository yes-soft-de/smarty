jQuery(document).ready( function($){
  //custom Sunset scripts
  var last_scroll = 0;
  // run this function at the first of js file to add reveal class to the posts that display in the first page
  revealPosts();

  /* Scroll Function */
  $(window).scroll(function () {
    var scroll = $(window).scrollTop();
    if ( Math.abs( scroll - last_scroll ) > $(window).height() * 0.1 ) {
      last_scroll = scroll;
      $('.page-limit').each(function () {
        // Check If This Element .page-limit is inside our window screen vision, $(this) refer to the .page-limit element
        if ( isVisible( $(this) ) ) {
          /*
          * history.replaceState() :
          * it's a default javascript and html5 declaration to push a new state inside the url
          * it's function to dynamically update url without refresh the page and it's supported by everything
          * */
          history.replaceState( null, null, $(this).attr('data-page') );
          return(false);
        }
      });
    }
  });

  /* Helper Functions */
  function revealPosts() {

    var posts = $('article:not(.reveal)'),
      i = 0;
    setInterval(function () {
      // Check If There Is Posts Or Not
      if ( i >= posts.length ) return false;
      var element = posts[i];
      // after retrieving our posts using ajax function we have to rerun the carousel function to apply it to other gallery posts
      $(element).addClass('reveal').find('.sunset-carousel-thumb').carousel();
      i++;
    }, 500);
  }

  function isVisible(element) {
    var scroll_position = $(window).scrollTop(),
      window_height = $(window).height(),
      element_top = $(element).offset().top,
      element_height = $(element).height(),
      element_bottom = element_top + element_height;
    // Check If Our Element Is Inside Window Screen
    return ( (element_bottom - element_height * 0.25 > scroll_position ) && ( element_top < ( scroll_position + 0.5 * window_height ) ) );
  }


  $(document).on('click', '#topic', function (e) {
    e.preventDefault();

    var that = $(this),
        llms_meditations = $('#llms-loop-item-meditations'),
        ajaxUrl = that.data('url'),
        courseId = that.data('id');

    $.ajax({
      url: ajaxUrl,
      dataType: 'html',
      type: 'post',
      data: {
        courseId: courseId,
        action: 'smart_way_load_more'
      },
      error: function ( response ) {
        console.log( 'error : ', response );
      },
      success: function ( response ) {
        llms_meditations.html('');
        $('.topic').removeClass('bg-less-dark-blue');
        that.find('.topic').addClass('bg-less-dark-blue');
        if ( response == 0 ) {
          llms_meditations.append('<div class="alert text-white text-center" style="background: #30124E"><h3>There isn\'t any lesson for this course</h3></div>');
        } else {
          llms_meditations.append(response);
        }
      }

    });



  });


  // Course Tabs In 'Syllabus file' To Display By Default
  // $('.llms-syllabus-wrapper #myTab li.nav-item:first-of-type').find('.nav-link').addClass('show active');
  $('.llms-syllabus-wrapper #myTab li.nav-item:first-of-type').find('.nav-link').addClass('show active');
  $('.llms-syllabus-wrapper #myTabContent div.tab-pane:first-of-type').addClass('show active');


  // Carousel For Courses
  $('.courses-carousel').slick({
    dots: true,
    infinite: true,
    speed: 300,
    slidesToShow: 3,
    slidesToScroll: 3,
    responsive: [
      {
        breakpoint: 600,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ]
  });

  // Carousel For Live Video
  $('.live-video-carousel').slick({
    // dots: true,
    infinite: true,
    speed: 300
  });

  // Carousel For Live Video
  $('.parent-content-box-carousel').slick({
    // dots: true,
    autoPlay: true,
    infinite: true,
    speed: 300
  });

  // Carousel For Live Video
  $('.meditation-carousel').slick({
    slidesToShow: 10,
    slidesToScroll: 1,
    // dots: true,
    centerMode: true,
    focusOnSelect: true,
    responsive: [
      {
        breakpoint: 1400,
        settings: {
          slidesToShow: 9,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 1300,
        settings: {
          slidesToShow: 8,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 1200,
        settings: {
          slidesToShow: 7,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 1050,
        settings: {
          slidesToShow: 6,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 900,
        settings: {
          slidesToShow: 5,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 850,
        settings: {
          slidesToShow: 5,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 770,
        settings: {
          slidesToShow: 4,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 600,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 500,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 400,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
    ]
  });

});
