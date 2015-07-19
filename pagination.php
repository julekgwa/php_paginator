<?php
	// find the current page
	$current_page = basename($_SERVER['SCRIPT_FILENAME']);

	// remove the .php extension and get the page number
	$page_number = rtrim($current_page, '.php');

	// create a function for pagination links of pages before the current page.
	// the function will have 2 parameters, one for the current page number and one for 
	// the number of pages before the current page.
	function prev_pages($page_number,$num_prev_pages){
		$list_items = ''; // save all list items.
		while ($num_prev_pages >= 1) {
			$page_number -= 1;
			// adding items to the list only if they are positive numbers
			if($page_number >= 1){
				$list_items = '<li><a href="' . $page_number . '.php">' . $page_number . '</a></li>' . $list_items; 
			}
			$num_prev_pages -= 1;
		}
		return $list_items;
	}

	
?>