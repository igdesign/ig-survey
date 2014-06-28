/**
 * Submit Form
 */
var form = document.getElementById("entryForm");

form.addEventListener("submit", function(e)
{
	e.preventDefault();
  var f = e.target,
      formData = '';
	// fetch form values
  for (var i = 0, d, v; i < f.elements.length; i++) {
      d = f.elements[i];
      if (d.name && d.value) {
          v = (d.type == "checkbox" || d.type == "radio" ? (d.checked ? d.value : '') : d.value);
          if (v) formData += d.name + "=" + escape(v) + "&";
      }
  }


	request = new XMLHttpRequest();
  request.open('POST', 'http://php.igdesign.ca/survey/', true);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');


  request.onload = function() {
    if (request.status >= 200 && request.status < 400) {
      // Success!

        data = JSON.parse(request.responseText);
        console.log(data);
        window.location.href = "/thank-you.html";
    } else {
      // We reached our target server, but it returned an error
      console.log(response);
    }
  };

  request.onerror = function() {
    // There was a connection error of some sort
    console.log('error');
  };

  request.send(formData);
});