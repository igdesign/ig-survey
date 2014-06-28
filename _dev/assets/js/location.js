request = new XMLHttpRequest();
request.open('GET', 'http://php.igdesign.ca/survey/index.php?location', true);

request.onload = function() {
  if (request.status >= 200 && request.status < 400){
    // Success!
    resp = request.responseText;

    var locationField = document.getElementById('location');
        locationField.value = resp;

  } else {
    // We reached our target server, but it returned an error

  }
};

request.onerror = function() {
  // There was a connection error of some sort
};

request.send();