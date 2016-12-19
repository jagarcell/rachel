// GLOBAL HANDLER FOR INTERNET CONNECTION CHECKING INTERVAL
var conninterval = null;

$(document).ready(function()
{
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
function chkinter() 
{
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
      $(".updatetd").show();
    }
    else
    {
      // NO, OFFLINE
      $("#updatebtn").prop("disabled", true);
      $("#updatebtn").prop("value", "WORKING OFFLINE");
      $(".updatetd").hide();
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
  div.innerHTML =  "<iframe id='rachel' style='width: 96%; height: 600px;' src='php/browse.php?url=http://dev.worldpossible.org/cgi/rachelmods.pl' onLoad='iframeContentChanged(web)'></iframe>";

  var divLog = document.getElementById('rsynclog');
  divLog.innerHTML = "";
  $("#rsynclog").hide();
  $("#rsynclogheader").hide();
};

function showhome()
{
  // NAVIGATES TO THE HOME PAGE
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

// CHANGE THE ORIGINAL LINKS FOR THE MODULE DOWNLOAD FRAME
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
  var logDivHeader = document.getElementById('rsynclogheader');
  logDivHeader.style.display ='none';

  // CREATE AN IFRAME ELEMENT HOSTING THE SELECTED SITE WITH
  // AN EVNT TO HANDLE THE DOCUMENT CONTENT ONCE IT IS LOADED
  // THE DOCUMENT CONTENT IS DOWNLOADED FROM PHP BY browsemod.php
  divmod.innerHTML =  "<iframe id='rachelmod' style='width: 98%; height: 600px; float: left' src='php/browsemod.php?url=" +
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
  // GO BACK TO HOME PAGE
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
          // IF THIS IS A LINK TO A DOWNLOADABLE SITE ...
          if(aElements[i].href == aElements[i].textContent)
          {
            // ... WE GET THE LINK AND CONFIGURE THE
            // CLICK EVENT FOR A DOWNLOAD FUNCTION
            enlace = aElements[i].href;
            aElements[i].onclick = function(){download(comandoDeDescarga, enlace)};
          }
          
          // WHEN WE FIND THE Sign Up ELEMENT ...  
          if(aElements[i].textContent == 'Sign Up')
          {
            // SIGN UP FOUND, CHANGE IT TO ...
            if(data == 'EXISTENTE')
            {
              // ... UPDATE IF EXISTENT
              aElements[i].textContent = 'UPDATE';
            }
            else
            {
              // ... DOWNLOAD IF NON EXISTENT
              aElements[i].textContent = 'DOWNLOAD';
            }
            // DELETE THE ORIGINAL LINK
            aElements[i].href = "";

            // ASSIGN A HANDLER TO THE UPDATE/DOWNLOAD FUNCTION
            aElements[i].onclick = function(){download(comandoDeDescarga, enlace)};
          }

         // CHANGE THE LOGIN LINK TO DOWNLOAD TOO
          if(aElements[i].textContent == 'Login')
          {
            aElements[i].textContent = 'SITE'; 
            aElements[i].href = "";
            aElements[i].onclick = function(){download(comandoDeDescarga, enlace)};
          }
        }
      }
    );
  }
};

// FUNCTION TO DOWNLOAD THE SITE
var downloadconsole = "connecting to repository ... <br>";

// THE FUNCTION TO HANDLE THE SITE DOWNLOAD
function download(command, link)
{
  var logDiv = document.getElementById('rsynclog');
  var logDivHeader = document.getElementById('rsynclogheader');

  var moddiv = document.getElementById('mod');
  var rachelmod = document.getElementById('rachelmod');
  var iframemodDocument = rachelmod.contentWindow.document;

  var tdElements = iframemodDocument.getElementsByTagName('td');
  for (var i = 0; i < tdElements.length; i++) {
    var td = tdElements[i];
    if(td.innerHTML == 'Title')
    {
      var title = tdElements[i+1].innerHTML;
    }
    if(td.innerHTML == 'Description')
    {
      var description = tdElements[i+1].innerHTML;
      break;
    }
  }

  var imgElements = iframemodDocument.getElementsByTagName('img');
  var img = imgElements[0];
   
  moddiv.style.width = '50%';

  logDiv.style.display = 'inline';
  logDivHeader.style.display = 'inline';

  // FOR THE PHP REQUEST
  $.post("../php/download.php",
    {
      rsync: command,
      link: link,
      description: description,
      imgsrc: img.src,
      title: title
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
    }
  );

  // SETS A 3 SECONDS DELAY TO START THE CONSOLE READ
  setTimeout(function() {readConsole(command)}, 5000);
};

// STARTS THE CONSOLE READ
var interval;
function readConsole(command)
{
  // CHECK THE CONSOLE EVERY 1 SECOND
  interval = setInterval(function(){intervalConsoleRead(command)}, 1000);
}

// CONSOLE READ INTERVAL
function intervalConsoleRead(command)
{
  // GET THE DIV TO SHOW THE CONSOLE READ
  var logDiv = document.getElementById('rsynclog');
  // AJAX POST TO READ THE CONSOLE
  $.post("../php/con.php",
    {
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

      if(data.search("total size") != -1)
      {
        clearInterval(interval);
        logDiv.innerHTML += "<br>DOWNLOAD COMPLETED";
        logDiv.scrollTop = topPos;
      }
    }
  );
}

// FUNCTION TO UPDATE THE OFFLINE SITE
function updatesite(rownum) 
{
  // CONFIGURE THE ID OF THE UPDATE LINK FOR THIS ROW
  var id = "#update_" + rownum;

  // SAVE THE UPODATE LINK HTML CONTENT
  var tdHtml = $(id).html();

  // CONFIGURE THE ID OF THE OFFLINE SITE'S LINK FOR THIS ROW
  var linkId = "#link_" + rownum;

  // SAVE THE ACTUAL LINK TO THIS SITE
  var linkHref = $(linkId).attr("href");

  // DISABLE THE OFFLINE SITE'S
  // NAVIGATION WHILE IT IS UPDATING
  $(linkId).removeAttr("href");

  // STARTS SHOWING THE DOWNLOAD ACTIVITY
  $(id).html("Updating<br>");

  // SETS THE DOWNLOAD ACTIVITY FUNCTION
  var interval = setInterval(function(){
    updating(id);
  }, 500);

  // REQUEST THE UPDATE OF THE SITE
  $.post("../php/updatesite.php",
    {
      rownum: rownum
    },
    function(data, status){
      // AT THE END OF THE UPDATE WE RESTORE THE
      // UPDATE LINK TO ITS ORIGINAL CONTENT
      $(id).html(tdHtml);
      // STOPS THE DOWNLOAD ACTIVITY FUNCTION
      clearInterval(interval);
      // RESTORE THE OFFLINE SITE'S LINK
      $(linkId).attr("href", linkHref);
    }
  );

  // SHOW A UPDATING ACTIVITY
  function updating(id) {
    // SAVE THE CURRENT PROGRESS     
    var idHtml = $(id).html();
    // IF WE GOT THE MAX LENGTH FOR THE PROGRESS MESSAGE ...
    if(idHtml.length > 20)
    {
      // ... RESTART IT
      $(id).html("Updating<br>");
    }
    else
    {
      // ... OTHERWISE ADD A PROGRESS INDICATOR
      $(id).html($(id).html() + ">");
    }
  }
}

function deletesite(rownum) {
  // body...
  alert(rownum);
}