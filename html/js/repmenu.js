$(document).ready(function(){
	// ONCE THE DOCUMENT IS LOADED LET'S
	// THE OFFLINE SITES LINK'S MENU

	// SHOW THE PREVIOUS HIDEN MENU DIV
	$("#repmenu").show();
	// ACTION TO BUILD THE MENU
	$.get("../php/repmenu.php",
	function(data, status)
	{
		// DATA COMES WITH THE LINKS OF THE OFFLINE SITES
		$("#repmenu").html($("#repmenu").html() + "<br>" + data);
	});	
});