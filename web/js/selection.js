/**
 * @file
 *
 * Handles selection in localStorage elements.
 */

$(document).ready(function () {
  // Deal with start and end date ///////////////////////////////////////////////////

  // If we can't retrieve start or end date from localStorage, we create them.
  var startString = localStorage.getItem('startDate');
  var endString = localStorage.getItem('endDate');

  var minDate = period.start.substring(0,10).split('-') ;
  minDate = minDate[2] + '/' + minDate[1] + '/' + minDate[0];
  var maxDate = period.end.substring(0,10).split('-') ;
  maxDate = maxDate[2] + '/' + maxDate[1] + '/' + maxDate[0];

  if (startString == null || startString == null) {
    var now = new Date();
    startDate = new Date(new Date().setMonth(now.getMonth() - 6));
    startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();
    localStorage.setItem('startDate', startString);

    endDate = now;
    endString = endDate.getDate() < 10 ? '0' + endDate.getDate() : endDate.getDate();
    endString = '/';
    endString += endDate.getMonth() < 9 ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    endString = '/';
    endString += endDate.getFullYear();
    localStorage.setItem('endDate', endDate);
  }

  // Initiate datepicker.
  $('#start-date').val(startString);
  $('#end-date').val(endString);
  $('.input-daterange').datepicker({
    format: 'dd/mm/yyyy',
    endDate: maxDate,
    startDate: minDate,
    language: 'fr'
  });

  // Add event on refresh button.
  $('.pilea-select-date').click(function(e) {
    // Store new value in localStorage.
    localStorage.setItem('startDate', $("#start-date").val());
    localStorage.setItem('endDate', $("#end-date").val());

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });

  $('.pilea-select-period a').click(function(e) {

    var now = new Date();
    var startDate = new Date();
    var endDate = new Date();
    switch (e.target.getAttribute('data')) {
      case 'current-week':
        startDate.setDate(now.getDate() - (now.getDay() - 1));
        endDate.setDate(now.getDate() - 1);
        break;
      case 'current-month':
        startDate.setDate(1);
        endDate.setDate(now.getDate() - 1);
        break;
      case 'current-year':
        startDate.setDate(1);
        startDate.setMonth(0);
        endDate.setDate(now.getDate() - 1);
        break;
      case 'last-week':
        startDate.setDate(now.getDate() - (now.getDay() + 6));
        endDate.setDate(now.getDate() - now.getDay());
        break;
      case 'last-month':
        startDate.setDate(1);
        startDate.setMonth(now.getMonth() - 1);
        endDate.setDate(0);
        break;
      case 'last-year':
        startDate.setDate(1);
        startDate.setMonth(0);
        startDate.setFullYear(now.getFullYear() - 1);
        endDate.setMonth(0);
        endDate.setDate(0);
        break;
      default:
        startDate = new Date(period.start);
        endDate = new Date(period.end);
    }

    var startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();

    var endString = endDate.getDate() < 10 ? '0' + endDate.getDate() : endDate.getDate();
    endString += '/';
    endString += endDate.getMonth() < 9 ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    endString += '/';
    endString += endDate.getFullYear();

    localStorage.setItem('startDate', startString);
    localStorage.setItem('endDate', endString);

    $('#start-date').val(startString);
    $('#end-date').val(endString);

    $('.input-daterange').datepicker('destroy');
    $('.input-daterange').datepicker({
      format: 'dd/mm/yyyy',
      endDate: maxDate,
      startDate: minDate,
      language: 'fr'
    });

    e.preventDefault(); // avoid to execute the actual submit of the form.

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection', {start: startString, end: endString}));
  });

  // Deal with frequency ///////////////////////////////////////////////////

  //If we can't retrieve frequency from localStorage, we create it.
  var frequency = localStorage.getItem('frequency');

  if (frequency == null) {
    frequency = 'month';
    localStorage.setItem('frequency', frequency);
  }

  // Initiate frequency button label
  var frequencyLabel = $('.pilea-select-frequency [data="' + frequency + '"]')[0].innerHTML;
  $('.pilea-select-frequency button')[0].innerHTML = frequencyLabel;

  // Add event on change
  $('.pilea-select-frequency a').click(function(e) {
    localStorage.setItem('frequency', e.target.getAttribute('data'));
    $('.pilea-select-frequency button')[0].innerHTML =  e.target.innerHTML;

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));
  });
})
