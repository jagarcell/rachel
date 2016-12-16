// GLOBAL HANDLER FOR INTERNET CONNECTION CHECKING INTERVAL
var conninterval = null;

$(document).ready(function(){
  // LET'S DO THE INITIALIZATION STUFFS HERE

  // INITIALIZE THE UPDATE BUTTON STATE DISABLED
  $("#web").hide();

  // THIS IS AN INMEDIATE CHECKING FOR INTERNET CONNECTION
  chkinter();

  // SET A PERIODIC INTERNET CONNECTION CHECKING
  conninterval = setInterval(function () {
    // CHECK CONNECTION EVERY 5 SECONDS
    chkinter();
    }, 5000);
 });

// INTERNET CONNECTION CHECKING FUNCTION
function chkinter() {
  // CHECK THE CONNECTION TO THE INTERNET
  $.get("../php/chkinter.php",
  function(data, status)
  {
    // DO WE HAVE CONNECTION ?
    if(data.length > 0)
    {
      // YES, ONLINE
      $("#updatebtn").prop("disabled", false);
      $("#updatebtn").prop("value", "ADD OR UPDATE OFFLINE SITES");
    }
    else
    {
      // NO, OFFLINE
      $("#updatebtn").prop("disabled", true);
      $("#updatebtn").prop("value", "WORKING OFFLINE");
    }
  });
}

// SHOWS THE RACHEL REPOSITORY FOR DOWNLOADING OR UPDATING THE OFFLINE SITES
function showRachel()
{
  // HIDE THE REPOSITORY MENU'S DIV
  $("#repmenu").hide();
  
  // GET THE DIVS THAT HOST THE IFRAMES FOR BOTH THE
  // MENU OF SITES AND THE SITE SELECTED IN MENU

  // MENU OF SITES
  var div = document.getElementById('web');

  // SITE SELECTED
  var divmod = document.getElementById('mod');

  // HIDE THE SITE SELECTED
  divmod.style.display = 'none';

  // SHOW THE MENU OF SITES
  div.style.display = 'inline';

  // AS WE ARE SHOWING THE MENU OF SITES
  // THEN DISABLE  THE UPDATE MENU        
  $("#b1").prop('disabled', true);

  // CREATE AN IFRAME ELEMENT HOSTING THE MENU OF SITES WITH AN
  // EVENT TO HANDLE THE DOCUMENT CONTENT ONCE IT IS LOADED
  // THE DOCUMENT CONTENT IS DOWNLOADED FROM PHP BY browse.php
  div.innerHTML =  "<iframe id='rachel' style='width: 100%; height: 550px;' src='php/browse.php?url=http://dev.worldpossible.org/cgi/rachelmods.pl' onLoad='iframeContentChanged(web)'></iframe>";

  var divLog = document.getElementById('rsynclog');
  divLog.innerHTML = "";
  $("#rsynclog").hide();
};

function showhome()
{
  window.location = "http://www.rachel.com";
}
// EVENT HANDLER FOR THE MENU OF SITES LOADED
function iframeContentChanged(type)
{
  // CHANGE THE ORIGINAL LINKS FOR THE MENU OF SITES IN ORDER
  // TO RID THE LOCAL DOMAIN TO AVOID THE CROSS DOMAINS SECURITY
  changeLinks();
  clearInterval(conninterval);
  $("#updatebtn").prop("value", "BACK TO HOME PAGE");
  $("#updatebtn").prop("disabled", false);
  $("#updatebtn").click(function () {
    showhome();
  });
};

function changeLinks()
{
  // GET IFRAME DOCUMENT FOR THE MENU OF SITES
  var iframe = document.getElementById('rachel');
  var iframeDocument = iframe.contentWindow.document;
  // SET THE ONCLICK EVENT TO A FUNCION
  // TO PROCCESS THE NAVIGATION REQUEST
  for (var i = iframeDocument.links.length - 1; i >= 0; i--) 
  {
    iframeDocument.links[i].onclick = function(){iframeNavigated(this)};
  };
};

// HANDLE THE MENU OF SITES SELECTION
function iframeNavigated(elementId)
{
  // HIDE THE MENU OF SITES  
  var div = document.getElementById('web');
  div.style.display = 'none';

  // SHOW THE SELECTED SITE
  var divmod = document.getElementById('mod');
  divmod.style.display = 'inline';
  divmod.style.width = '100%';

  var logDiv = document.getElementById('rsynclog');
  logDiv.style.display ='none';

  // CREATE AN IFRAME ELEMENT HOSTING THE SELECTED SITE WITH
  // AN EVNT TO HANDLE THE DOCUMENT CONTENT ONCE IT IS LOADED
  // THE DOCUMENT CONTENT IS DOWNLOADED FROM PHP BY browsemod.php
  divmod.innerHTML =  "<iframe id='rachelmod' style='width: 100%; height: 550px; float: left' src='php/browsemod.php?url=" +
  elementId.href + "' onLoad='iframeModContentChanged(mod)'></iframe>";
};

// HERE WE TAKE CARE OF THE SELECTED SITE CONTENT
function iframeModContentChanged(type)
{
  // GET IFRAME DOCUMENT CONTENT FOR THE SELECTED SITE  
  var iframemod = document.getElementById('rachelmod');
  var iframemodDocument = iframemod.contentWindow.document;

  // CHECK IF THE RSYNC COMMAND LINE USED
  // TO DOWNLOAD THE SITE´S CONTENT IS PRESENT
  var rsync = iframemodDocument.getElementsByClassName('rsync');
  if (rsync.length > 0) 
  {
    var aElements = iframemodDocument.getElementsByTagName('a');
    // AS THE RSYNC COMMAND LINE IS PRESENT
    // LET´S ENABLE THE UPDATE BUTTON
//    var button = document.getElementById('b1');
//    button.disabled = false;
    // CHECK IF THE SITE IS PRESENT IN THE
    // TABLE OF ALREADY REGISTERED SITES
    fetchSite();
  }

  // CHANGE THE ORIGINAL LINKS FOR THE SELECTED SITE SO
  // THAT THEY DON'T REFERENCE THEIR ORIGINAL LOCATIONS
  changeModLinks(iframemodDocument);

  // HIDE THE FINAL PARAGRAPHS AS WE DON´T NEED THEM 
  var pElemets = iframemodDocument.getElementsByTagName('p');
  for(var i = 0; i < pElemets.length; i++)
  {
    pElemets[i].style.display = 'none';
  }
};

// THIS FUNCTION CHANGES THE LINKS OF THE ELEMENT HOSTED
// IN THE IFRAME CONTENT DOCUMENT FOR THE SELECTED SITE
function changeModLinks(iframemodDocument)
{
  // CHANGE ALL THE LINKS ONCLICK EVENTS
  // TO REDIRECT THE  NAVIGATION IN THE
  // SELECTED SITE TO THE MENU OF SITES PAGE  
  for (var i = iframemodDocument.links.length - 1; i >= 0; i--) 
  {
    var link = iframemodDocument.links[i];
    link.onclick = function(){iframeModNavigated(link)};
  };
};

// RETURN TO THE MENU OF SITES PAGE
function iframeModNavigated(elementId)
{
  showRachel();
};

// THIS FUNCTION FECTHS THE RSYNC COMMAND IN THE DATABASE
function fetchSite()
{
  // SELECTED SITE CONTENT DOCUMENT
  var iframemod = document.getElementById('rachelmod');
  var iframemodDocument = iframemod.contentWindow.document;

  // INPUT ELEMENTS ARRAY TO SEE IF WE HAVE THE RSYNC COMMAND LINE
  var rsyncElements = iframemodDocument.getElementsByClassName('rsync');

  // ABORT THE FETCH IF WE DON´T HAVE ANY RSYNC ELEMENT
  if(!rsyncElements)
  {
    return;
  }

  // ASSIGN AN VALUE TO THE COMMAND LINE VARIABLE
  var comandoDeDescarga = rsyncElements[0].value;

  var enlaceElements = iframemodDocument.getElementsByTagName('a');

  for(var i=0; i<enlaceElements.length;i++)
  {
    var enlace = enlaceElements[i].href;
    if(enlace == enlaceElements[i].textContent)
    {
      break;
    }
    else
    {
      enlace = '';
    }
  }

  if(comandoDeDescarga.length == 0)
  {
    // IF THERE IS NO COMMAND THEN ABORT THE FETCH
    return;
  }
  else
  {
    // AS WE FOUND A RSYNC COMMAND WE WILL FETCH THE DATABASE
    // TO FIDN OUT IF THE SITE IS ALREADY REGISTERED

    // FOR THE PHP REQUEST

    $.post("../php/fetch.php", 
      {
        comandoDeDescarga: comandoDeDescarga
      }, 
    // TO HANDLE THE FETCH READY STATE
      function(data, status)
      {
        // IF IT WAS CORRECTLY PROCCESSED ...
        // ... PROCEED TO NEXT STEP
        
        // LET´S CHANGE THE ORIGINAL SIGN UP LINK TO
        // UPDATE OR DOWNLOAD ACCORDING TO THE COMMAND'S REGISTER STATUS
        var aElements = iframemodDocument.getElementsByTagName('a');
        // SEARCH THE <a> ELEMENTS TO FIND THE SIGN UP
        for(var i = 0; i < aElements.length; i++)
        {
          if(aElements[i].href == aElements[i].textContent)
          {
            enlace = aElements[i].href;
            aElements[i].onclick = function(){download(comandoDeDescarga, enlace)};
          }
            
          if(aElements[i].textContent == 'Sign Up')
          {
            var downloadElement = iframemodDocument.getElementById('download');
 
            // SIGN UP FOUND, CHANGE IT TO ...
            if(data == 'EXISTENTE')
            {
              // ... UPDATE IF EXISTENT
  //            button.textContent = 'UPDATE';
              aElements[i].textContent = 'UPDATE';
              // DELETE THE ORIGINAL LINK
              aElements[i].href = "";
            }
            else
            {
              // ... DOWNLOAD IF NON EXISTENT
              aElements[i].textContent = 'DOWNLOAD';
//              button.textContent = 'DOWNLOAD';
              // DELETE ORIGINAL LINK
              aElements[i].href = "";
            }
            // ASSIGN A HANDLER TO THE UPDATE/DOWNLOAD FUNCTION
            aElements[i].onclick = function(){download(comandoDeDescarga, enlace)};          }
           // CHANGE THE LOGIN LINK TO MODULES
            if(aElements[i].textContent == 'Login')
            {
              aElements[i].textContent = 'MODULES'; 
            }
        }
      }
    );
  }
};

// FUNCTION TO DOWNLOAD THE SITE
var downloadconsole = "connecting to repository ... <br>";
var chklog;
var readPending;
function download(command, link)
{
  var logDiv = document.getElementById('rsynclog');

  var moddiv = document.getElementById('mod');
  var rachelmod = document.getElementById('rachelmod');
  var iframemodDocument = rachelmod.contentWindow.document;

  var tdElements = iframemodDocument.getElementsByTagName('td');
  for (var i = 0; i < tdElements.length; i++) {
    var td = tdElements[i];
    if(td.innerHTML == 'Description')
    {
      var description = tdElements[i+1].innerHTML;
      break;
    }
  }

  moddiv.style.width = '50%';

  logDiv.style.display = 'inline';

  chklog = true;
  readPending = true;

  // FOR THE PHP REQUEST
  $.post("../php/download.php",
    {
      rsync: command,
      link: link,
      description: description
    },
  // TO HANDLE THE FETCH READY STATE
    function(data, status)
    {
      // IF IT WAS CORRECTLY PROCCESSED ...
      // ... PROCEED TO NEXT STEP
      if(data.length > 0)
      {
        downloadconsole += data;
        logDiv.innerHTML += data;
      }
      readPending = false;
    }
  );

  setTimeout(function() {readConsole(command)}, 5000);
  return;
};

var nline;
var interval;
function readConsole(command)
{
  nline = 0;
  interval = setInterval(function(){intervalConsoleRead(command)}, 1000);
}

function intervalConsoleRead(command)
{
  var logDiv = document.getElementById('rsynclog');
  $.post("../php/con.php",
    {
      nline: nline,
      command: command
    },
    function(data, status){
      if(data.length == 0)
      {
        console.log('no DATA');
        clearInterval(interval);
        return;
      }
      if(data == "<p id='last'><p>")
      {
        downloadconsole += "> ";
        if(downloadconsole.length > 58)
        {
          downloadconsole = "connecting to repository ... <br>";
        }
      }

      var logHtml = logDiv.innerHTML;

      logHtml = data;
      logDiv.innerHTML = downloadconsole + "<br>" + logHtml;

      try
      {
        var topPos = document.getElementById('last').offsetTop;
        logDiv.scrollTop = topPos;
      }
      catch(err)
      {
        console.log(err.message + " ID = " + id);
      }

      nline += data.length - 1;


      if(data.search("total size") != -1)
      {
        readPending = false;
        console.log('read done');
        clearInterval(interval);
      }
    }
  );
}
