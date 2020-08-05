<?php

//    showlink.php
//    Shows the info regarding a link
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
//    $Id: showlink.php,v 1.7 2000/10/16 16:06:35 angusgb Exp $

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
   if (!isset($IDUrl) || !isset($TagPosition) || !isset($AttrPosition))
   {
      // Error
      DisplayErrMsg($strMissingInfo);
      echo "<A href=\"index.php?dbname=". $dbname . "\">". $strhome."</A>";
      die;
   }

   $strSQL = "Select Schedule.Url as DestUrl, SrcUrl.Url as SourceUrl," .
      " Schedule.Status, Link.IDUrlSrc, Link.IDUrlDest," .
      " Link.LinkType, LinkResult, Link.Anchor, " .
      " Url.Url, Url.StatusCode, Url.ReasonPhrase, Url.ContentType," . 
      " HtmlStatement.Statement, HtmlAttribute.Attribute, HtmlAttribute.Content" .
      " from Url AS SrcUrl, Schedule, Link, HtmlStatement, HtmlAttribute" .
      " LEFT JOIN Url ON Url.IDUrl=Link.IDUrlDest " .
      " WHERE Link.IDUrlSrc=" . $IDUrl . " AND " .
      " Link.TagPosition=" . $TagPosition . " AND " .
      " Link.AttrPosition=" . $AttrPosition . " AND " .
      " SrcUrl.IDUrl=Link.IDUrlSrc AND " .
      " HtmlStatement.IDUrl=" . $IDUrl . " AND " .
      " HtmlAttribute.IDUrl=" . $IDUrl . " AND " .
      " HtmlStatement.TagPosition=Link.TagPosition AND " .
      " HtmlAttribute.TagPosition=Link.TagPosition AND " .
      " HtmlAttribute.AttrPosition=Link.AttrPosition AND " .
      " Schedule.IDUrl=Link.IDUrlDest";

      $result=$MyDB->Query($dbname, $strSQL);
   
   if ($result)
   {
      DisplayErrMsg($MyDB->errmsg);
      die;
   }
   
   if (! $num=$MyDB->NumRows())
   {
      DisplayErrMsg($strNoOccurrencies);
      die;
   }

   // Information retrieved
   $link = $MyDB->FetchArray();
   $URLName = $link["SourceUrl"];
   $ShowDestLink = $link["DestUrl"];
   if (strlen($link["Anchor"]))
      $ShowDestLink = $ShowDestLink . "#" . $link["Anchor"];

   $pagetitle = "Database: $dbname - " . $strShowLink . ": " . $URLName;
   $linkbar = "<A href=\"index.php?dbname=". $dbname . "\">". $strhome ."</A>";
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
<H4><%= $strShowLink %>:</H4>
<BLOCKQUOTE>
 <B><%= $strReferencingUrl %></B>: <A href="showurl.php?dbname=<%=$dbname%>&IDUrl=<%=$link["IDUrlSrc"]%>"><%= $URLName %></A><BR>
 <B><%= $strReferencedUrl %></B>: <A href="showurl.php?dbname=<%=$dbname%>&IDUrl=<%=$link["IDUrlDest"]%>"><%= $link["DestUrl"] %></A><BR>
<?php
  if ($link["Anchor"])
  {
?>
 <B><%= $strAnchor %></B>: <%= $link["Anchor"] %><BR>
<?php  
  }
?>
 <B><%= $strRetrievingFlag %></B>: <%= $link["Status"] %><BR>
 <B><%= $strLinkType %></B>: <%= GetTextString($link["LinkType"])  %><BR>
 <B><%= $strLinkResult %></B>: <%= GetTextString($link["LinkResult"])  %><BR>
</BLOCKQUOTE>

<H4><%= $strRetrievingInfo %></H4>
<BLOCKQUOTE>
<?php
   if ($link["StatusCode"])
   {
?>
 <B><%= $strStatusCode %></B>: <%= $link["StatusCode"] %> -
 <B><%= $strReasonPhrase %></B>: <%= $link["ReasonPhrase"] %> - 
 <B><%= $strContentType %></B>: <%= $link["ContentType"] %><BR>
<?php
   }
   else
   {
?>
<B><%= $strNotRetrieved %></B><BR>
<?php
   }
?>
</BLOCKQUOTE>

<H4><%=$strLinkIssued %></H4>
<BLOCKQUOTE>
<B><%= $strHTMLStatement %></B>: &lt;<%= $link["Statement"] %>&gt;<BR>
<B><%= $strHTMLAttribute %></B>: <%= $link["Attribute"] %>="<%= $link["Content"] %>"<BR>
</BLOCKQUOTE>

<H4><%= $strOperations %></H4>
<MENU>
<LI><A href="<%= $URLName %>" target="_blank"><%= $strOpenReferencingUrl %></A> (<%= $URLName %>)</LI>
<LI><A href="<%= $ShowDestLink %>" target="_blank"><%= $strOpenReferencedUrl %></A> (<%= $ShowDestLink %>)</LI>
</MENU>
<?php      

   $MyDB->Free();
}

?>

<BR>

<?php include ("include/footer.inc"); ?>
