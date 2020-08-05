<?php

//    include/functions.inc
//    General functions
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
//    $Id: functions.inc,v 1.6 2000/10/16 16:06:57 angusgb Exp $

if ( defined( '__FUNCTIONS_INC' ) ) return;
define( '__FUNCTIONS_INC', 1 );

include ("include/global.inc");

function DisplayErrMsg($errmsg)
{

?>
   <B><%= $errmsg %></B><BR>
<?php

}


///////
   // Page Management
///////

function WritePageLink($initpage,$pagesize,$count,$dbname,$otherinfo="")
{
   $nextoffset=$initpage+$pagesize;
   $justone=false;
   global $PHP_SELF;
   global $strNext;
   global $strPrevious;

   if ($initpage >= $pagesize)
   {
      $justone=true;
      
      if ($initpage >= $pagesize*2)
      {
         // Show the first page Link
         global $strFirst;
?>
   | <A href="<%= $PHP_SELF %>?dbname=<%= $dbname %>&initpage=0&pagesize=<%= $pagesize %>&count=<%= $count %><%=$otherinfo%>"><%= $strFirst %></A>
<?php         
      }
?>
   | <A href="<%= $PHP_SELF %>?dbname=<%= $dbname %>&initpage=<%= $initpage-$pagesize %>&pagesize=<%= $pagesize %>&count=<%= $count %><%= $otherinfo %>"><%= $strPrevious %></A>
<?php
   }
      
   if ($nextoffset < $count)
   {
      $justone=true;
?>
   | <A href="<%= $PHP_SELF %>?dbname=<%= $dbname %>&initpage=<%= $nextoffset %>&pagesize=<%= $pagesize %>&count=<%= $count %><%=$otherinfo%>"><%= $strNext %></A>
<?php
      if ($nextoffset+$pagesize < $count)
      {
         // Show the last page Link
         global $strLast;
         $lastoffset=((int)((int)($count-1)/$pagesize))*$pagesize;
?>
   | <A href="<%= $PHP_SELF %>?dbname=<%= $dbname %>&initpage=<%= $lastoffset %>&pagesize=<%= $pagesize %>&count=<%= $count %><%=$otherinfo%>"><%= $strLast %></A>
<?php         
      }

   }

   if ($justone) echo "|";
}


function ExpiredSession()
{
?>
<%= $strSessionExpired %><BR>
<?php
}


function GetTextString($str)
{
   $str2 = "\$str" . (string) $str;
   eval("global $str2;");
   eval("\$tmp=$str2;");
   return $tmp;
}

?>
