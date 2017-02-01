var adminpassword;

$(document).ready(
	function(){
		$("#config").hide();
		$.get("../php/config.php",
			{mode:'get'},
			function(data, status){
				var config = JSON.parse(data);
				if(config["error"] == true)
				{
					alert(config["message"]);
					return;
				}
				$("#ssid").val(config['ssid']);
				$("#pwd").val(config['pwd']);
				$("#wificard").val(config['wificard']);
				
				var ip = config['ip'];
				var ips = ip.split('.');
				$("#ip1").val(ips[0]);
				$("#ip2").val(ips[1]);
				$("#ip3").val(ips[2]);
				$("#ip4").val(ips[3]);
				
				var sip = config['sip'];
				var sips = sip.split('.');
				$("#sip1").val(sips[0]);
				$("#sip2").val(sips[1]);
				$("#sip3").val(sips[2]);
				$("#sip4").val(sips[3]);
				
				var eip = config['eip'];
				var eips = eip.split('.');
				$("#eip1").val(eips[0]);
				$("#eip2").val(eips[1]);
				$("#eip3").val(eips[2]);
				$("#eip4").val(eips[3]);

				$("#concession").val(config['concession']);

				var mask = config['mask'];
				var masks = mask.split('.');
				$("#mask1").val(masks[0]);
				$("#mask2").val(masks[1]);
				$("#mask3").val(masks[2]);
				$("#mask4").val(masks[3]);

				$("#clients").val(config['clients']);
				$("#clients").attr("max", config['maxclients']);
				
				$("#homepage").val(config['homepage']);
				$("#adminpassword").val("password");

				adminpassword = config['adminpassword'];
				console.log("ADMIN PASS = " + adminpassword);	
				validateiprange();
				$("#password").focus();
			}
		);
	}
);

function ipaddress(){
	// body...
	var iface = $('#wificard').val();

	$.post("../php/getipadd.php",
		{iface:iface},
		function(data, status)
		{
			data = data.trim();
			var ips = data.split(".");

			$("#ip1").val(ips[0]);
			$("#ip2").val(ips[1]);
			$("#ip3").val(ips[2]);
			$("#ip4").val(ips[3]);
		}
	);
}

function configsave() {
	// body...
	adminpassword = passwd2hash($("#adminpassword").val());

	$.post("../php/config.php",
		{
			mode:'set',

			ssid:$("#ssid").val(),
			pwd:$("#pwd").val(),
			wificard:$("#wificard").val(),

			ip1:$("#ip1").val(),	
			ip2:$("#ip2").val(),	
			ip3:$("#ip3").val(),	
			ip4:$("#ip4").val(),	

			mask1:$("#mask1").val(),
			mask2:$("#mask2").val(),
			mask3:$("#mask3").val(),
			mask4:$("#mask4").val(),

			sIp1:$("#sip1").val(),	
			sIp2:$("#sip2").val(),	
			sIp3:$("#sip3").val(),	
			sIp4:$("#sip4").val(),	

			eIp1:$("#eip1").val(),	
			eIp2:$("#eip2").val(),	
			eIp3:$("#eip3").val(),	
			eIp4:$("#eip4").val(),

			concession:$("#concession").val(),

			homepage:$("#homepage").val(),
			adminpassword: adminpassword
		},

		function(data, status){
			var config = JSON.parse(data);
			if(config["error"] == false)
			{
				var str = config["message"];
				$.get("../php/restartservices.php",
					function(data, status){
						alert(str);
						showhome();
					});
			}
			else
			{
				alert(config["message"]);
			}
		}
	);
}

function showhome()
{
  // NAVIGATES TO THE HOME PAGE
  $.get("../php/config.php",
    {mode:'get'},
    function(data, status)
    {
      var config = JSON.parse(data);
      if(config["error"] == false)
      {
  		window.location = "http://" + config['homepage'];
      }
      else
      {
	    alert(config["message"]);
      }
    });
}

function maxclients() {
	// body...
	console.log("maxclients");
	var mask1 = $("#mask1").val();
	var mask2 = $("#mask2").val();
	var mask3 = $("#mask3").val();
	var mask4 = $("#mask4").val();
	var maxclients = (255 - mask1 + 1)*(255 - mask2 + 1)*
					(255 - mask3 + 1)*(255 - mask4) - 3;
	$("#clients").attr("max", maxclients);

	validateiprange();
}

function validateiprange() {
	// body...
	chkip('4');
	chkip('3');
	chkip('2');
	chkip('1');
}

function chkip(ipn) {
	// body...
	if(
		(($("#ip" + ipn).val() & $("#mask" + ipn).val()) != ($("#sip" + ipn).val() & $("#mask" + ipn).val())) || 
		(($("#ip" + ipn).val() & $("#mask" + ipn).val()) != ($("#eip" + ipn).val() & $("#mask" + ipn).val()))
		)
	{
		document.getElementById("ip" + ipn).style.backgroundColor = "red";
		document.getElementById("sip" + ipn).style.backgroundColor = "red";
		document.getElementById("eip" + ipn).style.backgroundColor = "red";
		alert("IP OUT OF RANGE");
	}
	else
	{
		document.getElementById("ip" + ipn).style.backgroundColor = "";
		document.getElementById("sip" + ipn).style.backgroundColor = "";
		document.getElementById("eip" + ipn).style.backgroundColor = "";
	}
}

function showconfig() {
	// body...
	if(getAccess(document.getElementById("password").value, 
		adminpassword))
	{
		$("#login").hide();
		$("#config").show();
		$("#adminpassword").val(document.getElementById("password").value);
	}
	else
	{
		alert("THE PASSWORD IS INCORRECT");
	}
}

function getAccess(key, pwdhash) {
	// body...
	var hash = passwd2hash(key);

	if(hash == pwdhash || pwdhash == 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function passwd2hash(key) {
	// body...
	var hash = key.charCodeAt(0);

	var chr;
	for(var i = 1; i < key.length; i++)
	{
		chr = key.charCodeAt(i);
		hash = ((hash << 5) - hash) + chr;
	}

	return hash;
}
