<?php

//    drop.php
//    Drop an ht://Check database
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
//    $Id: drop.php,v 1.2 2000/10/16 16:06:35 angusgb Exp $

///////
   //    Global settings
///////

include ("include/global.inc");


if (!isset($dbname))
{
   // Error
   DisplayErrMsg($strErrorNoDBSelected);
   echo "<A href=\"index.php\">" . $strMainPage . "</A>";
   die;
}

   if (! isset($confirmed))
      $delete=0;
   else $delete=1;
   
   $pagetitle = "$strDBDeletion: '" . $dbname . "'. Take "
      . $delete+1 . "/2";
   $linkbar = "<A href=\"index.php\">" . $strMainPage . "</A>";

   if (! $delete)
      $linkbar = $linkbar . " | " .
         "<A href=\"javascript:history.go(-1)\">" . $strBack . "</A>";

?>
<?php include ("include/header.inc"); ?>
<?php

   if ($delete)
   {
      $result=$MyDB->Drop($dbname);
   
      if ($result)
      {
         DisplayErrMsg($MyDB->errmsg);
         die;
      }

      eval("\$strdeleted = \" $strDBDeleted \";");
   
?>
<P><%= $strdeleted %></P>

<?php
   }
   else
   {
?>
<P><%= $strDBDropSure %></P>

<MENU>
<LI><A href="<%= $PHP_SELF %>?dbname=<%= $dbname %>&confirmed=1"><%= $strDBDrop %></A></LI>
<LI><A href="index.php?dbname=<%= $dbname %>"><%= $strDBCancel %></A></LI>
</MENU>

<?php
   }
?>
<?php include ("include/footer.inc"); ?>
