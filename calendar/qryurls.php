<?php

//    qryurls.php
//    Shows a List of URLs previously set with a form
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
//    $Id: qryurls.php,v 1.2 2000/10/16 16:06:35 angusgb Exp $

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

$strSection=$strListofUrls;
$pagetitle = "Database: $dbname - " . $strSection;
eval("\$strhome = \"$strDBHome \";");
$linkbar = "<A href=\"index.php?dbname=". $dbname . "\">". $strhome."</A>";

// A database has been selected

if (!isset($session))
{
   include ("include/header.inc");
   ExpiredSession();
}

include ("include/header.inc");

if (isset ($cmd))
{

   if (!isset($initpage))
      $initpage=0;

   if (!isset($pagesize))
      $pagesize=30;

   $strwhere = "WHERE 1 ";
   $otherinfo = "&cmd=$cmd";
   
   if (isset($ctype) && $ctype!="AllCTypes")
   {
      $strwhere = $strwhere . "AND Url.ContentType $whatctype '$ctype' ";
      $otherinfo = $otherinfo . "&whatctype=$whatctype"
         . "&ctype=$ctype";
   }

   if (isset($scode) && $scode!="AllSCodes")
   {
      $strwhere = $strwhere . "AND Url.StatusCode $whatscode '$scode' ";
      $otherinfo = $otherinfo . "&whatscode=$whatscode"
         . "&scode=$scode";
   }

   if (isset($size) && strlen($size) && settype($size, "integer"))
   {
      $size *= 1024;
      $strwhere = $strwhere . "AND Url.Size $whatsize $size AND Url.Size >= 0 ";
      $otherinfo = $otherinfo . "&whatsize=$whatsize"
         . "&size=$size";
   }

   if (isset($sizeadd) && strlen($sizeadd) && settype($sizeadd, "integer"))
   {
      $sizeadd *= 1024;
      $strwhere = $strwhere . "AND (Url.SizeAdd + Url.Size) $whatsizeadd $sizeadd "
         . "AND Url.Size >= 0 ";
      $otherinfo = $otherinfo . "&whatsizeadd=$whatsizeadd"
         . "&sizeadd=$sizeadd";
   }


   $num=count($url); // Count the elements

   // Set the URL filter   
   for ($i=0; $i<$num; $i++)
   {
      if (isset($url[$i]) && $url[$i] && strlen($url[$i]) > 0)
      {
         if(isset($whaturl[$i]))
         {
            $strwhere = $strwhere . "AND Url.Url " . $whaturl[$i]
               . " '" . $url[$i] . "' ";
            $otherinfo = $otherinfo . "&whaturl[$i]=$whaturl[$i]"
               . "&url[$i]=$url[$i]";
          }
      }
   }


   $num=count($title); // Count the elements

   // Set the title filter   
   for ($i=0; $i<$num; $i++)
   {
      if (isset($title[$i]) && $title[$i] && strlen($title[$i]) > 0)
      {
         if(isset($whattitle[$i]))
         {
            $strwhere = $strwhere . "AND Url.Title " . $whattitle[$i]
               . " '" . $title[$i] . "' ";
            $otherinfo = $otherinfo . "&whattitle[$i]=$whattitle[$i]"
               . "&title[$i]=$title[$i]";
          }
      }
   }
   
   // Show all the URLs, retrieved ones as well as not
   $strGenSQL = "SELECT * "
      . "FROM Url "
      . $strwhere
      . " LIMIT " . $initpage . ", " . $pagesize;

   if (!isset($count))
   {
      $strCountSQL="select count(*) from Url "
         . $strwhere;

      $count = $MyDB->CountEntries($strCountSQL,$dbname,true);
      if ($count<0)
      {
         DisplayErrMsg($MyDB->errmsg);
         return;
      }
      $MyDB->Free();
   }

?>

<?php

// HTTP results
   
?>

<!--
SQL: <%= $strGenSQL %>
-->

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
else
{
?>
<P><%= $strFilterUrls %>:</P>

<FORM action="<%= $PHP_SELF %>" method="GET">
<INPUT type="hidden" name="dbname" value="<%= $dbname %>">
<TABLE border="1" cellpadding="5" cellspacing="2">

<TR>
<TH>
<%= $strUrl %> : 
</TH>
</TR>

<?php
   for ($i=0; $i<$NumFilterRows; $i++)
   {
?>

<TR>
<TD>
<SELECT name="whaturl[<%= $i %>]">
 <OPTION value="LIKE"><%= $strLike %></OPTION>
 <OPTION value="NOT LIKE"><%= $strNotLike %></OPTION>
 <OPTION value="REGEXP"><%= $strRegExp %></OPTION>
 <OPTION value="NOT REGEXP"><%= $strNotRegExp %></OPTION>
</SELECT>
<INPUT name="url[<%= $i %>]" type="text" value="<%= $url[$i] %>"
   size="30" maxlength="255">
</TD>
</TR>
<?php
   }
?>


<?php

   // Retrieving all the Status Codes found
   $strSQL="SELECT count(*) as Count, StatusCode "
      . "FROM Url GROUP BY StatusCode "
      . "ORDER BY StatusCode ASC";

   $result=$MyDB->Query($dbname, $strSQL);
   
   if ($result)
   {
      DisplayErrMsg($MyDB->errmsg);
      die;
   }
   
   if ($MyDB->NumRows())
   {

?>
<TR>
<TD>
<B><%= $strStatusCode %> : </B>
<SELECT name="whatscode">
 <OPTION value="="><%= $strLike %></OPTION>
 <OPTION value="!="><%= $strNotLike %></OPTION>
</SELECT>
<SELECT name="scode">
 <OPTION value="AllSCodes"><%= $strAllSCodes %></OPTION>
<?php
      while ($row = $MyDB->FetchArray())
      {
?>
 <OPTION value="<%= $row["StatusCode"] %>">
   <%= $row["StatusCode"] %> (<%= $row["Count"] %>)</OPTION>
<?php
      }
?>
</SELECT>
</TD>
</TR>
<?php
   }

   $MyDB->Free();

?>

<?php

   // Retrieving all the ContentTypes found
   $strSQL="SELECT count(*) as Count, ContentType "
      . "FROM Url GROUP BY ContentType "
      . "ORDER BY ContentType ASC";

   $result=$MyDB->Query($dbname, $strSQL);
   
   if ($result)
   {
      DisplayErrMsg($MyDB->errmsg);
      die;
   }
   
   if ($MyDB->NumRows())
   {

?>
<TR>
<TD>
<B><%= $strContentType %> : </B>
<SELECT name="whatctype">
 <OPTION value="="><%= $strLike %></OPTION>
 <OPTION value="!="><%= $strNotLike %></OPTION>
</SELECT>
<SELECT name="ctype">
 <OPTION value="AllCTypes"><%= $strAllCTypes %></OPTION>
<?php
      while ($row = $MyDB->FetchArray())
      {
?>
 <OPTION value="<%= $row["ContentType"] %>">
   <%= $row["ContentType"] %> (<%= $row["Count"] %>)</OPTION>
<?php
      }
?>
</SELECT>
</TD>
</TR>
<?php
   }

   $MyDB->Free();

?>

<TR>
<TD>
<B><%= $strSize %> : </B>
<SELECT name="whatsize">
 <OPTION value=">"><%= $strGt %></OPTION>
 <OPTION value="<"><%= $strLt %></OPTION>
 <OPTION value="="><%= $strLike %></OPTION>
</SELECT>
<INPUT name="size" type="text" value="<%= $size %>"
   size="10" maxlength="9"> KBytes
</TD>
</TR>


<TR>
<TD>
<B><%= $strPageWeight %> : </B>
<SELECT name="whatsizeadd">
 <OPTION value=">"><%= $strGt %></OPTION>
 <OPTION value="<"><%= $strLt %></OPTION>
 <OPTION value="="><%= $strLike %></OPTION>
</SELECT>
<INPUT name="sizeadd" type="text" value="<%= $sizeadd %>"
   size="10" maxlength="9"> KBytes
</TD>
</TR>


<TR>
<TH>
<%= $strTitle %> : 
</TH>
</TR>

<?php
   for ($i=0; $i<$NumFilterRows; $i++)
   {
?>

<TR>
<TD>
<SELECT name="whattitle[<%= $i %>]">
 <OPTION value="LIKE"><%= $strLike %></OPTION>
 <OPTION value="NOT LIKE"><%= $strNotLike %></OPTION>
 <OPTION value="REGEXP"><%= $strRegExp %></OPTION>
 <OPTION value="NOT REGEXP"><%= $strNotRegExp %></OPTION>
</SELECT>
<INPUT name="title[<%= $i %>]" type="text" value="<%= $title[$i] %>"
   size="30" maxlength="255">
</TD>
</TR>
<?php
   }
?>



<TR>
<TD colspan="2" align="right">
<INPUT type="submit" name="cmd" value="<%= $strLinkSubmit %>">
</TD>
</TR>
</FORM>
</TABLE>

<P align="justify">
<%= $strHelpOnString %>
</P>
<P align="justify">
<%= $strHelpOnRegExp %>
</P>

<?php
}

?>

<BR>

<?php include ("include/footer.inc"); ?>
