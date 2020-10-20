"use strict";

function makeRequest(method, url, params, callback) {
  var httpRequest = new XMLHttpRequest();
  var encodedParams = "";
  for(let index in params) {
    encodedParams = encodedParams + "&" + index + "=" + encodeURIComponent(params[index]);
  }

  httpRequest.addEventListener('readystatechange', function() {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
      if (httpRequest.status === 200) {
        try {
          var response = JSON.parse(httpRequest.responseText);
        } catch(e) {
          showMessage("danger", "Erreur", "Une erreur interne est survenue, contactez le SiMDE si l'erreur subsite.");
        }
        if(response.status == 0) {
          //alert(response.success);
          callback(response);
        } else {
          showMessage("danger", "Erreur", response.error);
        }
      } else if(httpRequest.status === 302) {
        showMessage("danger", "Erreur", "Problème de chargement de la page, connexion expirée ?");
      } else {
        alert('Un problème est survenu avec la requête.');
      }
    }
  });
  httpRequest.open(method, url);
  httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  httpRequest.send(encodedParams);
}

function showMessage(type, state, message, duration = 5000) {
  document.getElementById("messageBox").classList.remove("d-none");
  document.getElementById("messageBox").classList.remove("alert-danger", "alert-success", "alert-warning");
  var classMessage = "alert-" + type;

  document.getElementById("messageBox").classList.add(classMessage);
  document.getElementById("messageBoxState").textContent = state;
  document.getElementById("messageBoxContent").textContent = message;

  setTimeout(hideMessageBox, duration);
}

document.getElementById("closeMessageBox").addEventListener("click", hideMessageBox);
function hideMessageBox() {
  document.getElementById("messageBox").classList.add("d-none");
}

function httpGetAsync(url, callback) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
            callback(xmlHttp.responseText);
    }
    xmlHttp.open("GET", url, true); // true for asynchronous
    xmlHttp.send(null);
}

//Functions that add / delete ML should call this to keep left menue updated (Mabe call it when we add / update current user subscriptions)
function reloadLeftMenu() {
  httpGetAsync("php/frags/leftmenu.php", function(response) {
    console.log(response);
    document.getElementById("leftMenu").innerHTML = response;
  });
}

document.getElementById("leftMenu").addEventListener("click", function(evt) {
  if (evt.target.matches('.expandbtn'))
    evt.target.toggleAttribute("unexpanded");
  else if(evt.target.matches('.selectorExpandBtn'))
    evt.target.parentNode.toggleAttribute("unexpanded");
});
