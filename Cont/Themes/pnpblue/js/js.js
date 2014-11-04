jQuery(function(){
			


	jQuery('#slider').camera({
		height: '427px',
		loader: 'bar',
		pagination: false,
		thumbnails: false,
		time: 5000,
		playPause: false,
		hover: false,
		barDirection: 'bottomToTop',
		navigation: false,
		
		
	});

});

$(document).ready(function() 
{
	var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ]; 
	var dayNames= ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]

	var newDate = new Date();

	newDate.setDate(newDate.getDate());
	$('#currdate').html(dayNames[newDate.getDay()] + " " + newDate.getDate() + ' ' + monthNames[newDate.getMonth()] + ' ' + newDate.getFullYear());

});