/**
 * @file
 *
 * Handles selection in localStorage elements.
 */

(function() {
  $(document).ready(function () {
    // Deal with places ///////////////////////////////////////////////////

    function initPlace() {
      //If we can't retrieve place from localStorage, we create it.
      var place = getCurrentPlace();

      if (place == null || !Object.keys(places).includes(place)) {
        place = Object.keys(places)[0];
        setCurrentPlace(place);
      }

      // Initiate place button label.
      $('.pilea-select-place').each((index, element) => {
        var button = $(element).children('button');
        var placeLabel = places[place].name;
        button[0].innerHTML = placeLabel;
      });

      initPeriod(place);
      initFrequency(place);

      // Add event on change.
      $('.pilea-select-place a').click(function(e) {
        place = e.target.getAttribute('data');
        setCurrentPlace(e.target.getAttribute('data'));
        $('.pilea-select-place button')[0].innerHTML =  e.target.innerHTML;

        initPeriod(place);
        initFrequency(place);

        e.preventDefault(); // avoid to execute the actual submit of the form.

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));
      });
    }

    // Deal with start and end date ///////////////////////////////////////////////////

    function initPeriod(place) {
      // Get min and max dates.
      var minDate = places[place].start.substring(0,10).split('-') ;
      minDate = minDate[2] + '/' + minDate[1] + '/' + minDate[0];
      var periodStart = new Date(places[place].start);
      var maxDate = places[place].end.substring(0,10).split('-') ;
      maxDate = maxDate[2] + '/' + maxDate[1] + '/' + maxDate[0];
      var periodEnd = new Date(places[place].end);

      // If we can't retrieve start or end date from localStorage, we create them.
      var startString = getCurrentStartDate();
      var endString = getCurrentEndDate();

      if (startString == null || endString == null) {
        var now = new Date();
        startDate = new Date(new Date().setMonth(now.getMonth() - 6));
        startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
        startString += '/';
        startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
        startString += '/';
        startString += startDate.getFullYear();
        setCurrentStartDate(startString);

        endString = now.getDate() < 10 ? '0' + now.getDate() : now.getDate();
        endString += '/';
        endString += now.getMonth() < 9 ? '0' + (now.getMonth() + 1) : (now.getMonth() + 1);
        endString += '/';
        endString += now.getFullYear();
        setCurrentEndDate(endString);
      }

      // Initiate datepicker.
      $('.pilea-start-date').val(startString);
      $('.pilea-end-date').val(endString);
      $('.input-daterange').datepicker({
        format: 'dd/mm/yyyy',
        endDate: maxDate,
        startDate: minDate,
        language: 'fr'
      });

      // Add event on refresh button.
      $('.pilea-select-date').click(function(e) {
        var startDate = $(e.target).parent().find(".pilea-start-date");
        var endDate = $(e.target).parent().find(".pilea-end-date");

        // Store new value in localStorage.
        setCurrentStartDate(startDate.val());
        setCurrentEndDate(endDate.val());

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));

        e.preventDefault();
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
          case 'last-week':
            startDate.setDate(now.getDate() - (now.getDay() + 6));
            endDate.setDate(now.getDate() - now.getDay());
            break;
          case 'current-month':
            startDate.setDate(1);
            endDate.setDate(now.getDate() - 1);
            break;
          case 'last-month':
            startDate.setDate(1);
            startDate.setMonth(now.getMonth() - 1);
            endDate.setDate(0);
            break;
          case 'last-3-months':
            startDate.setDate(1);
            startDate.setMonth(now.getMonth() - 3);
            endDate.setDate(now.getDate() - 1);
            break;
          case 'last-6-months':
            startDate.setDate(1);
            startDate.setMonth(now.getMonth() - 6);
            endDate.setDate(now.getDate() - 1);
            break;
            case 'current-year':
            startDate.setDate(1);
            startDate.setMonth(0);
            endDate.setDate(now.getDate() - 1);
            break;
            case 'last-year':
            startDate.setDate(1);
            startDate.setMonth(0);
            startDate.setFullYear(now.getFullYear() - 1);
            endDate.setMonth(0);
            endDate.setDate(0);
            break;
          case 'sliding-year':
            startDate.setDate(1);
            startDate.setMonth(now.getMonth() - 11);
            endDate.setDate(now.getDate() - 1);
            break;
          default:
            startDate = new Date(places[place].start);
            endDate = new Date(places[place].end);
        }

        if (+startDate < +periodStart) {
          startDate = new Date(places[place].start);
        }

        if (+endDate > +periodEnd) {
          endDate = new Date(places[place].end);
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

        setCurrentStartDate(startString);
        setCurrentEndDate(endString);

        $('.pilea-start-date').val(startString);
        $('.pilea-end-date').val(endString);

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
    }

    // Deal with frequency ///////////////////////////////////////////////////

    function initFrequency(place) {
      //If we can't retrieve frequency from localStorage, we create it.
      var frequency = getCurrentFrequency();

      if (frequency == null) {
        frequency = 'month';
        setCurrentFrequency(frequency);
      }

      // Initiate frequency button label
      $('.pilea-select-frequency').each((index, element) => {
        var button = $(element).children('button');
        var frequencyLabel = $(element).find('[data="' + frequency + '"]')[0].innerHTML;
        button[0].innerHTML = frequencyLabel;
      });

      // Add event on change
      $('.pilea-select-frequency a').click(function(e) {
        setCurrentFrequency(e.target.getAttribute('data'));
        $('.pilea-select-frequency button')[0].innerHTML =  e.target.innerHTML;

        e.preventDefault(); // avoid to execute the actual submit of the form.

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));
      });
    }

    // Deal with meteo ///////////////////////////////////////////////////
    function initMeteo() {
      //If we can't retrieve meteo from localStorage, we create it.
      var meteo = getCurrentMeteo();

      if (meteo == null) {
        meteo = 'dju';
        setCurrentMeteo(meteo);
      }

      // Initiate frequency button label
      $('.pilea-select-meteo').each((index, element) => {
        var button = $(element).children('button');
        var meteoLabel = $(element).find('[data="' + meteo + '"]')[0].innerHTML;
        button[0].innerHTML = meteoLabel;
      });

      // Add event on change
      $('.pilea-select-meteo a').click(function(e) {
        setCurrentMeteo(e.target.getAttribute('data'));
        $('.pilea-select-meteo button')[0].innerHTML =  e.target.innerHTML;

        e.preventDefault(); // avoid to execute the actual submit of the form.

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));
      });
    }

    initPlace();
    initMeteo();

  })

  // HELPERS - Getters
  var getCurrentPlace = function() {
    return localStorage.getItem(user + '.place');
  }

  var getCurrentStartDate = function() {
    place = getCurrentPlace();
    return localStorage.getItem(user + '.' + place + '.startDate');
  }

  function getCurrentEndDate() {
    place = getCurrentPlace();
    return localStorage.getItem(user + '.' + place + '.endDate');
  }

  function getCurrentFrequency() {
    place = getCurrentPlace();
    return localStorage.getItem(user + '.' + place + '.frequency');
  }

  function getCurrentMeteo() {
    return localStorage.getItem(user + '.meteo');
  }

  // HELPERS - Setters
  function setCurrentPlace(place) {
    return localStorage.setItem(user + '.place', place);
  }

  function setCurrentStartDate(startDate) {
    place = getCurrentPlace();
    return localStorage.setItem(user + '.' + place + '.startDate', startDate);
  }

  function setCurrentEndDate(endDate) {
    place = getCurrentPlace();
    return localStorage.setItem(user + '.' + place + '.endDate', endDate);
  }

  function setCurrentFrequency(frequency) {
    place = getCurrentPlace();
    return localStorage.setItem(user + '.' + place + '.frequency', frequency);
  }

  function setCurrentMeteo(meteo) {
    return localStorage.setItem(user + '.meteo', meteo);
  }

  exports.getPlace = getCurrentPlace;
  exports.getStartDate = getCurrentStartDate;
  exports.getEndDate = getCurrentEndDate;
  exports.getFrequency = getCurrentFrequency;
  exports.getMeteo = getCurrentMeteo;
})();