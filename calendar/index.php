<?php

//    index.php
//    Home page
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
//    $Id: index.php,v 1.10 2000/10/16 16:06:35 angusgb Exp $

include ("include/global.inc");

if (isset($dbname))
{
   $pagetitle = "Database: $dbname";
   $linkbar = "<A href=\"index.php\">" . $strMainPage . "</A>";
   $linkbar = $linkbar . " | " .
      "<A href=\"drop.php?dbname=" . $dbname . "\">" . $strDBDeletion . "</A>";

}
else
{
   $pagetitle = $strMainPage;
   $linkbar="";
   // Set a cookie for preventing a direct access to a DB
   setcookie("session", 1, $CookieLifeTime);

}

?>
<?php include ("include/header.inc"); ?>

<!-- Check the presence of any ht://Check databases -->
<?php

if (! isset($dbname) )
{
   // No database selected
   if ($MyDB->GetHtCheckDBList())
   {
      DisplayErrMsg($MyDB->errmsg);
      return;
   }

   $numdbs = count ($MyDB->HtDBs);

   if ($numdbs)
   {
      // At least one database found
      
?>
<H3><% eval("echo \"$strNumDBs\";") %></H3>

<%= $strChooseaDB %>:<BR>
<FORM>
   <SELECT name="dbname">
<?php
      for ($i = 0; $i < $numdbs; $i++)
      {
?>
      <OPTION><%= $MyDB->HtDBs[$i] %></OPTION>
<?php
      }
?>
   </SELECT>
   <INPUT type="submit" value="<%= $strSelectaDB %>">
</FORM>

<?php
   }
   else
   {
      // No database found
?>
<H3><% eval("echo \"$strNoDBs\";") %></H3>
<?php
   }
}
else
{
   // A database has been selected

   if (!isset($session))
      ExpiredSession();
   else
   {
?>
<% eval("echo \"$strDBSelected\";") %><BR>

<H3><%= $strGeneralInfo %>:</H3>
<?php

   $strSQL = "Select * from htCheck";

   // Get general info from htCheck table

   $result=$MyDB->Query($dbname, $strSQL);
   
   if ($result)
   {
      DisplayErrMsg($MyDB->errmsg);
      die;
   }
   
   if ($MyDB->NumRows())
   {

      $row = $MyDB->FetchArray();
?>

<%= $strUser %>: <B><%= $row["User"] %></B><BR>
<%= $strStartTime %>: <B><%= $row["StartTime"] %></B><BR>
<%= $strEndTime %>: <B><%= $row["EndTime"] %></B><BR>

<?php

   }
   
   $MyDB->Free();


   $result=$MyDB->GetGeneralInfo($dbname);

      if ($result<0)
      {
         DisplayErrMsg($MyDB->errmsg);
         return;
      }
      else
      {
?>

<%= $strServersEncountered %>: <B><%= number_format($MyDB->Info["Server"]) %></B><BR>
<%= $strScheduledURLS %>: <B><%= number_format($MyDB->Info["Schedule"]) %></B><BR>

<%= $strRetrievedURLS %>: <B><%= number_format($MyDB->Info["Url"]) %></B><BR>
<%= $strHTMLStatements %>: <B><%= number_format($MyDB->Info["HtmlStatement"]) %></B>
   (<%= $strHTMLAttributes %>: <B><%= number_format($MyDB->Info["HtmlAttribute"]) %></B>)<BR>
<%= $strLinks %>: <B><%= number_format($MyDB->Info["Link"]) %></B><BR>


<H3><%= $strOperations %>:</H3>
<MENU>
<LI><A href="qryurls.php?dbname=<%= $dbname %>"><%= $strShowListUrlsFltr %></A></LI>
<LI><A href="listlinks.php?dbname=<%= $dbname %>"><%= $strShowListLinks %></A></LI>
<LI><A href="listurls.php?dbname=<%= $dbname %>&all=true"><%= $strShowListUrls %></A></LI>
<!--
<LI><A href="listurls.php?dbname=<%= $dbname %>"><%= $strShowListUrlsSeen %></A></LI>
<LI><A href="listurls.php?dbname=<%= $dbname %>&StatusCode=404"><%= $strShowNotFoundUrls %></A></LI>
-->
</MENU>

<?php

      // HTTP results

      $strSQL = "Select StatusCode as '". $strStatusCode . "', ReasonPhrase as '"
         . $strReasonPhrase ."',"
         . " count(*) as '". $strNumber . "' from Url group by StatusCode, ReasonPhrase";

         ShowSummary($dbname, $strSQL, $strHTTPRequestsResults);

      // Servers seen

      $strSQL = "Select Server, Port, HttpServer as '"
         . $strWebServerInfo . "', HttpVersion as '" . $strProtocol . "',"
         . " Requests as '" . $strRequests . "' from Server where Requests > 1"
         . " order by Requests DESC";
   
         ShowSummary($dbname, $strSQL, $strServersSeenOrdIDServer);

      // Connection results

      $strSQL = "Select ConnStatus as '". $strConnectionStatus . "',"
         . " count(*) as 'Num.' from Url group by ConnStatus";
   
         ShowSummary($dbname, $strSQL, $strConnectionResults);

      // Content-Type results

      $strSQL = "Select ContentType as 'Content Type',"
         . " count(*) as '". $strNumber . "' from Url group by ContentType";
   
         ShowSummary($dbname, $strSQL, $strContentTypeResults);

      }
   }
}

function ShowSummary($dbname, $strSQL, $strHeader)
{
?>

<H4> <%= $strHeader %> </H4>

<?php

global $MyDB;

$result = $MyDB->CreateHTMLTable($strSQL, $dbname, true);

if ($result<0)
{
   DisplayErrMsg($MyDB->errmsg);
   return;
}
else if (!result):
?>
<P><%= $strNoOccurrencies %></P>
<?php
endif;
}

?>

<BR>

<?php include ("include/footer.inc"); ?>
