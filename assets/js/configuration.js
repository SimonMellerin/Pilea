$(document).ready(function() {
  $('.bootstrap-multiselect').multiselect({
    buttonText: function(options, select) {
      if (options.length === 0) {
          return 'personne';
      }
      else if (options.length > 5) {
          return 'plus de 5 personnes';
      }
      else {
          var labels = [];
          options.each(function() {
              if ($(this).attr('label') !== undefined) {
                  labels.push($(this).attr('label'));
              }
              else {
                  labels.push($(this).html());
              }
          });
          return labels.join(', ') + '';
      }
    }
  });
});