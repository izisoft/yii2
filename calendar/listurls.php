<?php

//    listurls.php
//    Shows a List of Urls
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
//    $Id: listurls.php,v 1.5 2000/10/16 16:06:35 angusgb Exp $

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
else
{
   if (!isset($initpage))
      $initpage=0;

   if (!isset($pagesize))
      $pagesize=30;
      
   if ($all)
   {
      // Show all the URLs, retrieved ones as well as not
      $strGenSQL = "Select Schedule.IDUrl, Schedule.Url, Url.StatusCode, Url.ReasonPhrase, Url.ConnStatus" .
         " from Schedule LEFT JOIN Url ON Schedule.IDUrl=Url.IDUrl" . 
         " order by Schedule.IDServer LIMIT " . $initpage . ", " . $pagesize;

      $strCountSQL="select count(*) from Schedule";

      $strSection=$strListofAllUrls;
      $otherinfo="&all=1"; // Keeps on showing the complete list
   }
   else
   {
      // Shows only the retrieved URLs
      $otherinfo="";

      $strSection=$strListofUrls;

      if (isset($StatusCode))
      {
         $strWhere=" WHERE StatusCode=" .$StatusCode;
         $otherinfo=$otherinfo . "&StatusCode=" . $StatusCode;
         $strSection = $strSection . " - " . $strStatusCode . "=" .
            $StatusCode;
      }
      else $strWhere="";
         
      $strGenSQL = "Select IDUrl, Url, StatusCode, ReasonPhrase, ConnStatus" .
         " from Url" . $strWhere .
         " order by IDServer LIMIT " . $initpage . ", " . $pagesize;
   
      $strCountSQL="select count(*) from Url" . $strWhere;

   }
   
   $pagetitle = "Database: $dbname - " . $strSection;
   eval("\$strhome = \"$strDBHome \";");
   $linkbar = "<A href=\"index.php?dbname=". $dbname . "\">". $strhome."</A>";

   if (!isset($count))
   {
      $count = $MyDB->CountEntries($strCountSQL,$dbname,true);
      if ($count<0)
      {
         DisplayErrMsg($MyDB->errmsg);
         return;
      }
      $MyDB->Free();
   }
      
?>
<?php include ("include/header.inc"); ?>

<?php

   // A database has been selected

   if (!isset($session))
      ExpiredSession();

// HTTP results
   
?>
<H4><%= $strSection %></H4>
<P>
<?
if ($count) printf ($strListPageInfo, number_format($initpage+1), number_format(($initpage+$pagesize)>$count?$count:$initpage+$pagesize), number_format($count));
?>  
<BR>
<% WritePageLink($initpage, $pagesize, $count, $dbname, $otherinfo) %>
</P>

<?php

   $result=$MyDB->Query($dbname, $strGenSQL);
   
   if ($result)
   {
      DisplayErrMsg($MyDB->errmsg);
      die;
   }
   
   if ($MyDB->NumRows())
   {
      // At least one occurrence found

?>
<TABLE border="1" cellpadding="2" cellspacing="2">
<TR>
<TH> N. </TH>
<TH> ID </TH>
<TH> Url </TH>
<TH> <%= $strStatusCode %> & <BR> <%= $strReasonPhrase %></TH>
<TH> <%= $strConnectionStatus %></TH>
</TR>
      
<?php
      $i=0;
      while ($row = $MyDB->FetchArray())
      {
         // Get next row
         $i++;
?>
 <TR>
   <TD align="right"> <%= number_format($initpage+$i) %> </TD>
   <TD align="right"> <%= number_format($row["IDUrl"]) %> </TD>
   <TD align="left"><A href="showurl.php?dbname=<%= $dbname %>&IDUrl=<%= $row["IDUrl"] %>"><%= $row["Url"] %></A></TD><?php
   if ($row["StatusCode"])
   {
?>
   <TD align="center"> <%= $row["StatusCode"] %> - <%= $row["ReasonPhrase"] %></TD>
   <TD align="center"> <%= $row["ConnStatus"] %></TD>
<?php
   }
   else
   {
?>
   <TD align="center" colspan="2"><EM><%= $strNotRetrieved %></EM></TD>
<?php   
   }
?>
 </TR>
<?php         
      }

?>
</TABLE>
<?php      
      // Page management

      WritePageLink($initpage, $pagesize, $count, $dbname,$otherinfo);
   }
   else
   {
?>
<P><%= $strNoOccurrencies %></P>

<?php
   }

   $MyDB->Free();

}

?>

<BR>

<?php include ("include/footer.inc"); ?>
