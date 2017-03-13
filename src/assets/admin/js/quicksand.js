// DOMContentLoaded
jQuery(function ($) {
	// bind dropdowns in the form
	var $filterType = $('#filter select[name="type"]');
	var $filterTags = $('#filter select[name="tags"]');
	var $filterActive = $('#filter select[name="sort"]');

	// get the first collection
	var $applications = $('#features');
	//var $application_clone = $applications.clone().prop('id', 'features_clone');
	// clone applications to get a second collection
	var $data = $applications.clone();

	//console.log($data);
	// attempt to call Quicksand on every form change
	$('select').change(function () {
		$filterType = $('#filter select[name="type"]').val();
		$filterTags = $('#filter select[name="tags"]').val();
		$filterActive = $('#filter select[name="sort"]').val();
		//console.log($data);
		$(this).addClass('animate');
		if ($filterType == '0') {
			if ($filterTags == '0') {
				if ($filterActive == '0') {
					//0-0-0
					var $filteredData = $data.find('li');
				} else {
					//0-0-1
					var $filteredData = $data.find('li[data-active=' + $filterActive + ']');
				}
			} else {
				if ($filterActive == '0') {
					//0-1-0
					var $filteredData = $data.find('li[data-tags=' + $filterTags + ']');
				} else {
					//0-1-1
					var $filteredData = $data.find('li[data-tags=' + $filterTags + ']' + 'li[data-active=' + $filterActive + ']');
				}
			}
		} else {
			if ($filterTags == '0') {
				if ($filterActive == '0') {
					//1-0-0
					var $filteredData = $data.find('li[data-type=' + $filterType + ']');
				} else {
					//1-0-1
					var $filteredData = $data.find('li[data-type=' + $filterType + ']' + 'li[data-active=' + $filterActive + ']');
				}
			} else {
				if ($filterActive == '0') {
					//1-1-0
					var $filteredData = $data.find('li[data-type=' + $filterType + ']' + 'li[data-tags=' + $filterTags + ']');
				} else {
					//1-1-1
					var $filteredData = $data.find('li[data-type=' + $filterType + ']' + 'li[data-tags=' + $filterTags + ']' + 'li[data-active=' + $filterActive + ']');
				}
			}
		}

		// finally, call quicksand
		$applications.quicksand($filteredData, {
			duration: 1000,
			//easing: 'easeInOutQuad',
			adjustHeight: 'auto'
		}, function () {
			$("a[rel*=leanModal]").leanModal();
		});
	});
});
