<?php

//    listlinks.php
//    Shows a List of Links previously set with a form
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
//    $Id: listlinks.php,v 1.5 2000/10/16 16:06:35 angusgb Exp $

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

$strSection=$strListofLinks;
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

   $strwhere = "";
   $otherinfo = "&cmd=$cmd";
   
   if (isset($linkresult) && $linkresult!="AllLinks")
   {
      $strwhere = $strwhere . "AND Link.LinkResult $whatlinkresult '$linkresult' ";
      $otherinfo = $otherinfo . "&whatlinkresult=$whatlinkresult"
         . "&linkresult=$linkresult";
   }
   
   if (isset($linktype) && $linktype!="AllLinks")
   {
      $strwhere = $strwhere . "AND Link.LinkType $whatlinktype '$linktype' ";
      $otherinfo = $otherinfo . "&whatlinktype=$whatlinktype"
         . "&linktype=$linktype";
   }

   $num=count($src); // Count the elements

   // Set the source filter   
   for ($i=0; $i<$num; $i++)
   {
      if (isset($src[$i]) && $src[$i] && strlen($src[$i]) > 0)
      {
         if(isset($whatsrc[$i]))
         {
            $strwhere = $strwhere . "AND Url.Url " . $whatsrc[$i]
               . " '" . $src[$i] . "' ";
            $otherinfo = $otherinfo . "&whatsrc[$i]=$whatsrc[$i]"
               . "&src[$i]=$src[$i]";
          }
      }
   }
   
   $num=count($dest); // Count the elements

   // Set the destination filter   
   for ($i=0; $i<$num; $i++)
   {
      if (isset($dest[$i]) && $dest[$i] && strlen($dest[$i]) > 0)
      {
         if(isset($whatdest[$i]))
         {
            $strwhere = $strwhere . "AND Schedule.Url " . $whatdest[$i]
               . " '" . $dest[$i] . "' ";
            $otherinfo = $otherinfo . "&whatdest[$i]=$whatdest[$i]"
               . "&dest[$i]=$dest[$i]";
          }
      }
   }

   // Show all the URLs, retrieved ones as well as not
   $strGenSQL = "SELECT Link.*, "
      . "Url.Url as UrlSrc, "
      . "Schedule.Url as UrlDest, "
      . "HtmlStatement.Statement "
      . "FROM Url, Schedule, Link "
      . "LEFT JOIN HtmlStatement "
      . "ON HtmlStatement.IDUrl = Link.IDUrlSrc "
      . "AND HtmlStatement.TagPosition = Link.TagPosition "
      . "WHERE Schedule.IDUrl = Link.IDUrlDest "
      . "AND Url.IDUrl = Link.IDUrlSrc "
      . $strwhere
      . " LIMIT " . $initpage . ", " . $pagesize;

   if (!isset($count))
   {
      $strCountSQL="select count(*) from Schedule, Url, Link "
         . "LEFT JOIN HtmlStatement "
         . "ON HtmlStatement.IDUrl = Link.IDUrlSrc "
         . "AND HtmlStatement.TagPosition = Link.TagPosition "
         . "WHERE Schedule.IDUrl = Link.IDUrlDest "
         . "AND Url.IDUrl = Link.IDUrlSrc "
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

<!--
<%=$strCountSQL %><BR>
Records found: <%=$count %><BR>
<%=$strGenSQL %><BR>
-->


<?php

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
<TABLE width="100%" border="1" cellpadding="2" cellspacing="2">
<TR>
<TH> N. </TH>
<TH> <%= $strReferencingUrl %> </TH>
<TH> <%= $strReferencedUrl %> </TH>
<TH> <%= $strHTMLStatement %> </TH>
<TH> <%= $strLinkType %> </TH>
<TH> <%= $strLinkResult %> </TH>
<TH><%= $strShow %></TH>
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
   <TD align="left"><A href="showurl.php?dbname=<%= $dbname %>&IDUrl=<%= $row["IDUrlSrc"] %>"><%= $row["UrlSrc"] %></A></TD>
   <TD align="left"><A href="showurl.php?dbname=<%= $dbname %>&IDUrl=<%= $row["IDUrlDest"] %>"><%= $row["UrlDest"] %></A></TD>
   <TD align="left"><%= $row["Statement"] %></TD>
   <TD align="center"><%= GetTextString($row["LinkType"])  %></TD>
   <TD align="center"><%= GetTextString($row["LinkResult"]) %></TD>
   <TD align="center">
<?php
   if (strcmp($row["LinkType"], "Redirection"))  // It's not a redirection
   {
?>
   <A href="showlink.php?dbname=<%=$dbname%>&IDUrl=<%=$row["IDUrlSrc"]%>&TagPosition=<%=$row["TagPosition"]%>&AttrPosition=<%=$row["AttrPosition"]%>"><%=$strShowLink%></A>
<?php
   }
?>
   </TD>
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
<P><%= $strFilterLinks %>:</P>

<FORM action="<%= $PHP_SELF %>" method="GET">
<INPUT type="hidden" name="dbname" value="<%= $dbname %>">
<TABLE border="1" cellpadding="5" cellspacing="2">

<?php

   // Retrieving all the LinkResults found
   $strSQL="SELECT count(*) as Count, LinkResult "
      . "FROM Link GROUP BY LinkResult "
      . "ORDER BY LinkResult ASC";

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
<TD colspan="2">
<B><%= $strLinkResult %> : </B>
<SELECT name="whatlinkresult">
 <OPTION value="="><%= $strLike %></OPTION>
 <OPTION value="!="><%= $strNotLike %></OPTION>
</SELECT>
<SELECT name="linkresult">
 <OPTION value="AllLinks"><%= $strAllLinks %></OPTION>
<?php
      while ($row = $MyDB->FetchArray())
      {
?>
 <OPTION value="<%= $row["LinkResult"] %>">
   <%= GetTextString($row["LinkResult"]) %> (<%= $row["Count"] %>)</OPTION>
<?php
      }
?>
</SELECT>
</TD>
</TR>

<?php

   }

   $MyDB->Free();

   // Retrieving all the LinkType found
   $strSQL="SELECT count(*) as Count, LinkType "
      . "FROM Link GROUP BY LinkType "
      . "ORDER BY LinkType ASC";

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
<TD colspan="2">
<B><%= $strLinkType %> : </B>
<SELECT name="whatlinktype">
 <OPTION value="="><%= $strLike %></OPTION>
 <OPTION value="!="><%= $strNotLike %></OPTION>
</SELECT>
<SELECT name="linktype">
 <OPTION value="AllLinks"><%= $strAllLinks %></OPTION>
<?php
      while ($row = $MyDB->FetchArray())
      {
?>
 <OPTION value="<%= $row["LinkType"] %>">
   <%= GetTextString($row["LinkType"]) %> (<%= $row["Count"] %>)</OPTION>
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
<TH>
<%= $strReferencingUrl %> : 
</TH>
<TH>
<%= $strReferencedUrl %> : 
</TH>
</TR>
<?php
   for ($i=0; $i<$NumFilterRows; $i++)
   {
?>
<TR>
<TD>
<SELECT name="whatsrc[<%= $i %>]">
 <OPTION value="LIKE"><%= $strLike %></OPTION>
 <OPTION value="NOT LIKE"><%= $strNotLike %></OPTION>
 <OPTION value="REGEXP"><%= $strRegExp %></OPTION>
 <OPTION value="NOT REGEXP"><%= $strNotRegExp %></OPTION>
</SELECT>
<INPUT name="src[<%= $i %>]" type="text" value="<%= $src[$i] %>"
   size="30" maxlength="255">
</TD>
<TD>
<SELECT name="whatdest[<%= $i %>]">
 <OPTION value="LIKE"><%= $strLike %></OPTION>
 <OPTION value="NOT LIKE"><%= $strNotLike %></OPTION>
 <OPTION value="REGEXP"><%= $strRegExp %></OPTION>
 <OPTION value="NOT REGEXP"><%= $strNotRegExp %></OPTION>
</SELECT>
<INPUT name="dest[<%= $i %>]" type="text" value="<%= $dest[$i] %>"
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
