function viewport() {
    var e = window, a = 'inner';
    if (!('innerWidth' in window )) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return e[ a+'Width' ];
}

(function($){
  $(function(){

    // Detect touch screen and enable scrollbar if necessary
    function is_touch_device() {
      try {
        document.createEvent("TouchEvent");
        return true;
      } catch (e) {
        return false;
      }
    }
    if (is_touch_device()) {
      $('#nav-mobile').css({ overflow: 'auto'})
    }

    // On resize adjust the padding bottom
    $('main').css('padding-bottom',$('footer').height()+'px');
    $(window).resize(function(){
        $('main').css('padding-bottom',$('footer').height()+'px');
    });

    // Plugin initialization
    $('.button-collapse').sideNav({'edge': 'left'});

    // Corrects the sidenav on initialization
    if (viewport() <= 992)
        $('#nav-mobile').css('left', '-105%');
    $(window).resize( function() {
        if (viewport() <= 992){
            $('#nav-mobile').css('left', '-105%');
        }else{
            $('#nav-mobile').css('left', '0');
        }
    });

  }); // end of document ready
})(jQuery); // end of jQuery name space
