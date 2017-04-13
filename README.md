## PHP Paginator 

PHP paginator is a script that can generate pagination links + next/previous page links. It gives you full control of how you want your pagination to appear and how many pagination links, to appear per page. 

## Features
- Generate pagination links.
- Create pagination files, by using PHP copy function.
- Customizable with your own CSS or CSS frameworks.
- Set how many pagination to appear per page. e.g. pagination($current_page, 4, 4), only 4 pagination will appear on both sides of the current page.

## Requirements
PHP 5+

## How to use

You can customize the appearance of the pagination by using your own CSS or other CSS Frameworks.
Here I'm using [Foundation 5](http://foundation.zurb.com/) 
```
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/foundation.css">
```
```
  <script src="js/vendor/modernizr.js"></script>`
  ```
## Creating the pagination file.
  ```php
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
$rows_left = $Db->rowsLeft('testing', $start, $limit); // "testing" is the name of the table

if($rows_left < $num_rows){
    $limit = $num_rows;
}

// selecting data from the database
$select = $Db->selecetLimit('testing', '*', $start, $limit); // "testing" is the name of the table

// from the data we have, we can now create pages by looping through the $last_page.
// we are going to create pages until the we reach the last page, by copying index.php or some other page.
for ($counter = 1; $counter <= $last_page; $counter++) { 
    $page = $counter . '.php';
    if(!file_exists($page)){
        copy('index.php', $page); // create pages if, they don't exists.
    }
}
?>
```
## Calling the pagination() function.
```html
<div class="pagination-centered">
   		<!-- our paginatio  using foundation 5-->
   		 <hr>
             <ul class="pagination" role="menubar" arial-label="Pagination">
                 <!-- hide previous if current page is index.php or some other page provided in the if statement, by applying the hide class to the li -->
                 <li class="arrow <?php echo ($current_page == 'index.php' ? 'hide' : ''); ?>" arial-disabled="true"><a href="<?php echo ($page_number - 1).'.php'; ?>">&laquo; Previous</a></li>
                 <!-- pagination goes here -->
                 <?php pagination($page_number, 4, 4); ?>
                 <!-- hide next if we have reached the last page, by applying the hide class to the li -->
                 <li class="arrow <?php echo ($last_page == $page_number ? 'hide' : ''); ?>" arial-disabled="true"><a href="<?php echo ($page_number + 1).'.php'; ?>">Next &raquo; </a></li>
             </ul>
   </div>
   ```
![](pagination.png)
