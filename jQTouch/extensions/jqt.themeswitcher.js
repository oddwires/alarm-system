(function() {
  if ($.jQT) {
    $.jQT.addExtension(function(jQT) {
      var switchStyleSheet;
      $('[data-switch-stylesheet]').live('tap', function() {
        switchStyleSheet($(this).attr('data-stylesheet-title'), $(this).attr('data-switch-stylesheet'));
        $('[data-switch-stylesheet]').removeClass('selected');
        $(this).addClass('selected');
        return false;
      });
      switchStyleSheet = function(newStyleTitle, newStyle) {
        var $link, newHref;
        // oddwires code - store the current style to a cookie on the device.
        var d =new Date();
        d.setTime(d.getTime()+(31*24*60*60*1000));  // cookie expires in 31 days
        var expires = "expires="+d.toGMTString();
        document.cookie = "theme=" + newStyleTitle +"; " + expires;
        document.cookie = "stylesheet=" + newStyle +"; " + expires;
        // end of oddwires code
        $link = $("link[title=\"" + newStyleTitle + "\"]");
        newHref = $link.length ? $link.attr('href') : newStyle;
        $('link[data-jqt-theme]').attr('href', newHref);
        return $('#jqt').attr('data-jqt-theme', newStyleTitle);
      };
      return {
        switchStyleSheet: switchStyleSheet
      };
    });
  }

}).call(this);
