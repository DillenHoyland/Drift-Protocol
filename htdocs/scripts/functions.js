// Light/ Dark mode switcher. Adapted from Source: https://getbootstrap.com/docs/5.3/customize/color-modes/

function checkMode() {
  // check for cookies
  const mode = checkCookie("darkmode");
  if (mode === "true" || mode === "false") setMode(mode);
  else setMode("true");
}

function setMode(mode) {
  const html = document.querySelector('html');
  const lightSwitch = document.getElementById('lightSwitch');
  if (mode === "") mode = lightSwitch.checked ? "true" : "false";
  html.setAttribute('data-bs-theme', (mode === "true"? "dark": "light"));
  lightSwitch.checked  = (mode === "true") ? true: false;
  const perm = checkCookie("cookiebar");
  if (perm === "CookieAllowed") setCookie("darkmode", mode, 30);
}

function checkRead() {
  // check for cookies
  const readVal = checkCookie("readmode");
  if (readVal === "true" || readVal === "false") setRead(readVal);
  else setRead("false");
}

function setRead(readVal) {
  const body = document.querySelector('body');
  const readMode = document.getElementById('readMode');
  if (readVal === "") readVal = readMode.checked ? "true" : "false";
  if (readVal === "true") {
    body.classList.remove("def");
    body.classList.add("acc");
  }
  else {
    body.classList.add("def");
    body.classList.remove("acc");
  }
  readMode.checked  = (readVal === "true") ? true: false;
  const perm = checkCookie("cookiebar");
  if (perm === "CookieAllowed") setCookie("readmode", readVal, 30);
}

// Source: https://www.w3schools.com/js/js_cookies.asp
function checkCookie(nameStr) {
  const name = nameStr + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function setCookie(cname,cvalue,exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  let expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}


function loginModal() {
    var registerModal = document.getElementById("registerModal");
    var loginModal = document.getElementById("loginModal");
    registerModal.style.display = "none";
    loginModal.style.display = "block";
  }
  
  function registerModal() {
    var loginModal = document.getElementById("loginModal");
    var registerModal = document.getElementById("registerModal");
    loginModal.style.display = "none";
    registerModal.style.display = "block";
  }
  
  function showModal(modalID) {
    if (modalID !== undefined) {
      var modal = document.getElementById(modalID);
      modal.style.display="block";
    }
  }
  function hideModal(modalID) {
    if (modalID !== undefined) {
      var modal = document.getElementById(modalID);
      modal.style.display="none";
    }
    // Otherwise close all conceivable Modals
    else {
      var modal = document.getElementsByClassName('modal');
      for (let i = 0; i <= 100; i++) {
          modal[i].style.display="none";
      }
    }
  }

