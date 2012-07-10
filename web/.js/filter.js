// "hello world".score("axl") //=> 0.0
// "hello world".score("ow") //=> 0.6
// "hello world".score("hello world") //=> 1.0

String.prototype.score = function(abbreviation, offset) {
  offset = offset || 0;

  if(abbreviation.length == 0) {
    return 0.9
  };

  if (abbreviation.length > this.length) {
    return 0.0
  };

  for (var i = abbreviation.length; i > 0; i--) {
    
    var sub_abbreviation = abbreviation.substring(0,i);

    var index = this.indexOf(sub_abbreviation);

    if (index < 0) {
      continue;
    };

    if (index + abbreviation.length > this.length + offset) {
      continue;
    };

    var next_string = this.substring(index+sub_abbreviation.length);
    var next_abbreviation = null;

    if (i >= abbreviation.length) {
      next_abbreviation = '';
    } else {
      next_abbreviation = abbreviation.substring(i);
    };

    var remaining_score = next_string.score(next_abbreviation,offset+index);

    if (remaining_score > 0) {
      var score = this.length-next_string.length;

      if (index != 0) {
        var j = 0;

        var c = this.charCodeAt(index-1);
        
        if (c==32 || c == 9) {
          
          for(var j=(index-2); j >= 0; j--) {
            c = this.charCodeAt(j);
            score -= ((c == 32 || c == 9) ? 1 : 0.15);
          };

        } else {
          score -= index;
        };

      };

      score += remaining_score * next_string.length;
      score /= this.length;
      return score;
    };
  };

  return 0.0;
};

(function($) {

  $.fn.filterItems = function(options) {
    
    $(this).on('keyup', function(e) {
      var search = $(this).val();

      if (!search || (search === "")) { $(options.parent).show() };

      $(options.by).each(function() {
        var score = $.trim($(this).text().toLowerCase()).score(search);

        if (score <= options.limit) {
          $(this).parents(options.parent).hide();
        } else {
          $(this).parents(options.parent).show();
        };

      });

    });

    return this;
  };

})(jQuery);

// Usage
$('li.filter input').filterItems({
    by: 'td.report-name',
    parent: 'tr.report',
    limit: 0.7
});
