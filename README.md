## Simple PHP Paginator 

PHP paginator is a script that can generate pagination links + next/previous page links. It gives you full control of how you want your pagination to appear and how many pagination links, to appear per page. 

##### Table of Contents

  * [Features](#features)
  * [Requirements](#requirements)
  * [How to use](#how-to-use)
  * [Creating pagination](#creating-pagination)
  * [Screenshots](#screenshot)

## Features
- Generate pagination links.
- Create pagination files, by using PHP copy function.
- Customizable with your own CSS or any CSS framework.

## Requirements
PHP 5+

## How to use

Include `Paginator.php` in your files.
```PHP
<?php

require_once 'Paginator.php';

$Paginator = new Paginator('mysql:host=localhost;dbname=ng_app', 'root', '000000');
$Paginator->setItemLimitPerPage(4);
$Paginator->setTable('comments');
$Paginator->createPages();
$Paginator->setCurrentPageClass('active');
$Paginator->setUrlPattern('/php_paginator/');
$numPrevPage = 4;
$numNextPage = 4;
$paginationCssClass = 'pagination';

?>
```


## Creating pagination
  ```HTML
  <div class="text-center">
  <!-- our pagination  using Bootstrap-->
  <hr>
    <?php
        $Paginator->pagination($Paginator->getPageNumber(), $numPrevPage, $numNextPage, $paginationCssClass);
    ?>
   </div
   ```
## Screenshots
![](pagination.png)
