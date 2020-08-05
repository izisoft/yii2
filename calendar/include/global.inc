<?php

//    include/global.inc
//    Global variables declaration
//
//    Part of the ht://Check package
//
//    Copyright (c) 1999-2000 Comune di Prato - Prato - Italy
//    Author: Gabriele Bartolini - Prato - Italy <angusgb@users.sourceforge.net>
//
//    For copyright details, see the file COPYING in your distribution
//    or the GNU General Public License version 2 or later 
//    <http://www.gnu.org/copyleft/gpl.html>
//
//    $Id: global.inc,v 1.8 2000/10/16 16:06:57 angusgb Exp $

if ( defined( '__GLOBAL_INC' ) ) return;
define( '__GLOBAL_INC', 1 );

///////
   //   General Info
///////

$Organisation = "PO-Net Rete Civica di Prato";

///////
   //    Language 
///////

// You can choose your language here (if present)
$Language="english";
//$Language="italian";

// Include it
include ("include/$Language.inc");

///////
   //    Common functions
///////

include ("include/functions.inc");

///////
   //   Database settings
///////

include ("include/mysqldb.inc");
$hostname = "localhost";
$username = "";
$password = "";

// Create a global instance of the database
$MyDB = new MySQLDB ($hostname, $username, $password);

///////
   //   Session settings
///////

$CookieLifetime = time() + 120;   // Set the lifetime of a cookie

///////
   //   List of links setting
///////

$NumFilterRows = 4;  //Set the number of filter rows in the listlinks.php page


?>
