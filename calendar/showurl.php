<?php

//    showurl.php
//    Shows the info of a either retrieved or not URL
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
//    $Id: showurl.php,v 1.7 2000/10/16 16:06:35 angusgb Exp $

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
   eval("\$strhome = \"$strDBHome \";");
   if (!isset($IDUrl))
   {
      // Error
      DisplayErrMsg($strErrorNoUrlSelected);
      echo "<A href=\"index.php?dbname=". $dbname . "\">". $strhome."</A>";
      die;
   }
   
   // Retrieve Information from the DB
   $strSQL = "Select Schedule.Url as SUrl, Schedule.HopCount," .
      " Schedule.IDReferer, " .
      " Url.*, Server.Server" .
      " from Schedule, Server" .
      " LEFT JOIN Url ON Schedule.IDUrl=Url.IDUrl" .
      " WHERE Schedule.IDUrl=" . $IDUrl . " AND " .
      " Schedule.IDServer=Server.IDServer";

   $result=$MyDB->Query($dbname, $strSQL);
   
   if ($result)
   {
      DisplayErrMsg($MyDB->errmsg);
      die;
   }
   
   if ($MyDB->NumRows()>1)
   {
      DisplayErrMsg($strErrorDuplicateKey);
      die;
   }
   else if ($MyDB->NumRows()==0)
   {
?>
<P><%= $strNoOccurrencies %></P>
<?php
   }
   else
   {

      $RefererUrl="";

      // Information retrieved
      $row = $MyDB->FetchArray();
      $MyDB->Free();

      // Get the referer
      if ($row["IDReferer"])
      {

         $strSQL = "Select Schedule.Url" .
            " from Schedule" .
            " WHERE Schedule.IDUrl=" . $row["IDReferer"] ;

         $result=$MyDB->Query($dbname, $strSQL);
         if ($result)
         {
            DisplayErrMsg($MyDB->errmsg);
            die;
         }

         $refrow = $MyDB->FetchArray();
         $RefererUrl=$refrow["Url"];
         $MyDB->Free();

      }
            
      $URLName = $row["SUrl"];
      $pagetitle = "Database: $dbname - " . $strShowUrl . ": " . $URLName;
      $linkbar = "<A href=\"index.php?dbname=". $dbname . "\">". $strhome."</A>";
      $linkbar = $linkbar . " | " .
         "<A href=\"javascript:history.go(-1)\">" . $strBack . "</A>";
      
?>
<?php include ("include/header.inc"); ?>
<?php

      // A database and a URL have been selected

      // Check for the session
      if (!isset($session))
         ExpiredSession();
?>
<H3><%= $URLName %></H3>
<?php
   if ($row["Url"])
   {
         // Info available - retrieved
?>         
<H4><%= $strInfoAvailable %>:</H4>
<BLOCKQUOTE>
<?php
      if ($row["Title"])
      {
?>
<B><%= $strTitle %></B>:  <%= $row["Title"] %><BR>
<?php
      }
?>
<B>Server</B>:  <%= $row["Server"] %><BR>
<B><%= $strContentType %></B>:  <%= $row["ContentType"] %><BR>
<B><%= $strLastModified %></B>:  <%= $row["LastModified"] %> GMT<BR>
</BLOCKQUOTE>

<H4><%= $strRetrievingInfo %>:</H4>
<BLOCKQUOTE>
<B><%= $strLastAccess %></B>:  <%= $row["LastAccess"] %> GMT<BR>
<B><%= $strStatusCode %></B>:  <%= $row["StatusCode"] %><BR>
<B><%= $strReasonPhrase %></B>:  <%= $row["ReasonPhrase"] %><BR>
<B><%= $strReferer %></B>:  <?php
   if ($RefererUrl) print $RefererUrl;
   else print ("-"); ?><BR>
<B><%= $strHopCount %></B>:  <%= $row["HopCount"] %><BR>
<?php
      if ($row["Location"])
      {
         // The URL has been redirected, so no outcoming info and size info
         // We should retrieve the Index of the redirected URL

      // Retrieve Information from the DB
      $strSQL = "Select IDUrl from Schedule" .
      " WHERE Url='" . $row["Location"] . "'";

      $result=$MyDB->Query($dbname, $strSQL);
   
      if ($resultloc)
      {
         DisplayErrMsg($MyDB->errmsg);
         die;
      }

      $rowloc = $MyDB->FetchArray();
      $IDLocation=$rowloc["IDUrl"];
      $MyDB->Free();
      
      
                  
?>
<B><%= $strLocation %></B>:
<A href="<%=$PHP_SELF%>?dbname=<%=$dbname%>&IDUrl=<%=$IDLocation%>"><%= $row["Location"] %></A><BR>
<?php
      }
?>
<B><%= $strConnectionStatus %></B>:  <%= $row["ConnStatus"] %><BR>
<B><%= $strTransferEncoding %></B>:  <%= $row["TransferEncoding"]?$row["TransferEncoding"]:"-" %><BR>
</BLOCKQUOTE>

<?php
      if (! $row["Location"])
      {
?>
<H4><%= $strSizeInfo %>:</H4>
<BLOCKQUOTE>
<B><%= $strSize %></B>:  <%= number_format($row["Size"]) %> Bytes<BR>
<B><%= $strSizeAdd %></B>:  <%= number_format($row["SizeAdd"]) %> Bytes<BR>
<B><%= $strPageWeight %></B>:  <%= number_format($row["Size"]+$row["SizeAdd"]) %> Bytes<BR>
</BLOCKQUOTE>
<?php
      }
?>

<H4><%= $strOutgoingLinks %>:</H4>
<?php

      // Info about outgoing links (both retrieved or not)
   
      $strSQL = "Select Schedule.Url, Schedule.Status, Link.*, " .
         " Url.StatusCode, Url.ReasonPhrase, Url.ContentType" . 
         " from Schedule, Link" .
         " LEFT JOIN Url ON Url.IDUrl=Link.IDUrlDest " .
         " WHERE Link.IDUrlSrc=" . $IDUrl . " AND " .
         " Schedule.IDUrl=Link.IDUrlDest" . 
         " ORDER BY Link.TagPosition, Link.AttrPosition";

      $result=$MyDB->Query($dbname, $strSQL);
   
      if ($result)
      {
         DisplayErrMsg($MyDB->errmsg);
         die;
      }
   
      if ($num=$MyDB->NumRows())
      {
?>
<B><%= $strRecordFound %></B>: <%= $num %><BR>
<TABLE border="1">
<TR>
<TH>Url</TH>
<TH><%= $strRetrievingFlag %></TH>
<TH><%= $strLinkType %></TH>
<TH><%= $strStatusCode %></TH>
<TH><%= $strReasonPhrase %></TH>
<TH><%= $strContentType %></TH>
<TH><%= $strShow %></TH>
</TR>
<?php      
         while ($link=$MyDB->FetchArray())
         {
?>
 <TR>
   <TD><A href="<%=$PHP_SELF%>?dbname=<%=$dbname%>&IDUrl=<%=$link["IDUrlDest"]%>"><%=$link["Url"]%></A></TD>
   <TD align="center"><%= $link["Status"] %></TD>
   <TD align="center"><%= $link["LinkType"] %></TD>
<?php
            if ($link["StatusCode"])
            {
?>
   <TD align="center"><%= $link["StatusCode"] %></TD>
   <TD align="center"><%= $link["ReasonPhrase"] %></TD>
   <TD align="center"><%= $link["ContentType"] %></TD>
<?php
            }
            else
            {
?>
   <TD colspan="3" align="center"><EM><%= $strNotRetrieved %></EM></TD>
<?php
            }
?>
   <TD align="center">
<?php
   if (strcmp($link["LinkType"], "Redirection"))  // It's not a redirection
   {
?>
   <A href="showlink.php?dbname=<%=$dbname%>&IDUrl=<%=$link["IDUrlSrc"]%>&TagPosition=<%=$link["TagPosition"]%>&AttrPosition=<%=$link["AttrPosition"]%>"><%=$strShowLink%></A>
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
      }
      else
      {
?>
<P><%= $strNoOccurrencies %></P>
<?php
      }
   }
   else
   {
?>
<EM><%= $strNotRetrieved %></EM><BR>
<?php
   }
?>
<H4><%= $strIncomingLinks %>:</H4>
<?php

      // Info about incoming links - only retrieved, of course
   
   $strSQL = "Select Url.IDUrl, Url.Url, Url.ContentType," .
      " Link.*" .
      " from Link, Url" .
      " WHERE Link.IDUrlDest=" . $IDUrl . " AND " .
      " Link.IDUrlSrc=Url.IDUrl" . 
      " ORDER BY Url.Url";

      $result=$MyDB->Query($dbname, $strSQL);
   
      if ($result)
      {
         DisplayErrMsg($MyDB->errmsg);
         die;
      }
   
      if ($num=$MyDB->NumRows())
      {
?>
<B><%= $strRecordFound %></B>: <%= $num %><BR>
<TABLE border="1">
<TR>
<TH>Url</TH>
<TH><%= $strLinkType %></TH>
<TH><%= $strContentType %></TH>
<TH><%= $strShow %></TH>
</TR>
<?php      
         while ($link=$MyDB->FetchArray())
         {
?>
 <TR>
   <TD><A href="<%=$PHP_SELF%>?dbname=<%=$dbname%>&IDUrl=<%=$link["IDUrlSrc"]%>"><%=$link["Url"]%></A></TD>
   <TD align="center"><%= $link["LinkType"] %></TD>
   <TD align="center"><%= $link["ContentType"] %></TD>
   <TD align="center">
<?php
   if (strcmp($link["LinkType"], "Redirection"))  // It's not a redirection
   {
?>
   <A href="showlink.php?dbname=<%=$dbname%>&IDUrl=<%=$link["IDUrlSrc"]%>&TagPosition=<%=$link["TagPosition"]%>&AttrPosition=<%=$link["AttrPosition"]%>"><%=$strShowLink%></A>
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
      }
      else
      {
?>
<P><%= $strNoOccurrencies %></P>
<?php
      }
   }
}

?>

<H4><%= $strOperations %></H4>
<MENU>
<LI><A href="<%= $row["SUrl"] %>" target="_blank"><%= $strOpenThisUrl %></A> (<%= $row["SUrl"] %>)</LI>
</MENU>

<BR>

<?php include ("include/footer.inc"); ?>
