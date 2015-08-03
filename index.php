<?php

require_once('pagination.php');
require_once('Db.php');

$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
$Db = new Db('mysql:host=localhost;dbname=data','root','',$options);

// get row count
$row_count = $Db->rowCounts('testing'); // "testing" is the name of the table

// how many records should be displayed on a page?
$num_rows = 12;

// get last page 
$last_page = ($row_count / $num_rows) - 1;
if(!is_int($last_page)){
    $last_page = (int)$last_page + 1;
}
$start = 0; // starting position (offset)
$limit = $num_rows; // number of records to return 

if($current_page !== 'index.php'){
    $start = ($limit * $page_number);
}
// get rows left in the database
$rows_left = $Db->rowsLeft('testing', $start, $limit);

if($rows_left < $num_rows){
    $limit = $num_rows;
}

// selecting data from the database
$select = $Db->selecetLimit('testing', '*', $start, $limit);

// from the data we have, we can now create pages by looping through the $last_page.
// we are going to create pages until the we reach the last page, by copying index.php or some other page.
for ($counter = 1; $counter <= $last_page; $counter++) { 
    $page = $counter . '.php';
    if(!file_exists($page)){
        copy('index.php', $page);
    }
}
?>
<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en" >

<head>
  <meta charset="utf-8">
  <!-- If you delete this meta tag World War Z will become a reality -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Foundation 5</title>

  <!-- If you are using the CSS version, only link these 2 files, you may add app.css to use for your overrides if you like -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/foundation.css">

  <!-- If you are using the gem version, you need this only -->
  <link rel="stylesheet" href="css/app.css">

  <script src="js/vendor/modernizr.js"></script>

</head>
<body>

<div class="row">
     <div class="column small-12 medium-9 large-9">
  <!-- body content here -->
  <?php 
  	//loop through the data from the database
   ?>
   <div class="pagination-centered">
   		<!-- our paginatio  using foundation 5-->
   		 <hr>
             <ul class="pagination" role="menubar" arial-label="Pagination">
                 <li class="arrow" arial-disabled="true"><a href="<?php echo ($page_number - 1).'.php'; ?>">&laquo; Previous</a></li>
                 <!-- pagination goes here -->
                 <?php pagination($page_number, 4, 4); ?>
                 <li class="arrow" arial-disabled="true"><a href="<?php echo ($page_number + 1).'.php'; ?>">Next &raquo; </a></li>
             </ul>
   </div>
	</div>
</div>
  <script src="js/vendor/jquery.js"></script>
  <script src="js/foundation.min.js"></script>
  <script>
    $(document).foundation();
  </script>
</body>
</html>
