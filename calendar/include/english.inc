<?php

//    include/english.inc
//    English sentences and messages
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
//    $Id: english.inc,v 1.14 2000/10/16 16:06:57 angusgb Exp $

if ( defined( '__ENGLISH_INC' ) ) return;
define( '__ENGLISH_INC', 1 );

/* Main Page */
$strMainPage   =  "Main Page";
$strNumDBs     =  "\$numdbs ht://Check databases have been found on \$hostname";
$strNoDBs      =  "Sorry. No ht://Check databases have been found on \$hostname";
$strChooseaDB  =  "Choose";
$strSelectaDB  =  "Select a database";

/* Main page of a database */

$strDBSelected =  "Database '<B>\$dbname</B>' selected";
$strGeneralInfo   = "General info";
$strServersEncountered  =  "Servers encountered";
$strScheduledURLS =  "Scheduled URLS (both visited and discarded)";
$strRetrievedURLS =  "Retrieved URLS";
$strHTMLStatements   =  "HTML statements";
$strHTMLAttributes   =  "HTML attributes";
$strLinks      =  "Links";
$strShowListUrls        =  "Show the complete URLs list";
$strShowListUrlsSeen    =  "Show the list of URLs that have been seen";
$strShowNotFoundUrls    =  "Show the list of not found URLs (Status code 404)";
$strShowListLinks       =  "Show the list of links retrieved";
$strShowListUrlsFltr    =  "Show the retrieved URLs list (with filter)";

$strHTTPRequestsResults    =  "Summary of HTTP requests results";
$strServersSeenOrdIDServer =  "Summary of HTTP Servers that have been seen (ordered by requests)";
$strConnectionResults      =  "Summary of Connection results";
$strUser    =  "MySQL user who performed the crawl";
$strStartTime  = "Start Time";
$strEndTime  = "End Time";

/* Sections */
$strListofUrls =  "List of retrieved Urls";
$strListofAllUrls =  "List of retrieved and not retrieved Urls";
$strListofLinks =  "List of the links";
$strShowUrl    =  "URL info";
$strShowLink   =  "Link info";
$strDBHome =  "'<B>\$dbname</B>' Home Page";
$strDBDeletion =  "Database deletion";
$strDBDeleted  =  "Database '<B>\$dbname</B>' dropped";

/* General */

$strSessionExpired   =  "Sorry. Session expired";
$strUrl              =  "URL";
$strRequests         =  "Requests";
$strProtocol         =  "Protocol";
$strConnectionStatus =  "Connection Status";
$strContentType      =  "Content type";
$strTransferEncoding =  "Transfer Encoding";
$strStatusCode       =  "Status Code";
$strReasonPhrase     =  "Reason Phrase";
$strNumber           =  "Number";
$strNoOccurrencies   =  "No occurrencies";
$strWebServerInfo    =  "Web Server Info";
$strLastModified     =  "Last modified time";
$strLastAccess       =  "Last access time";
$strLocation         =  "Location";
$strSize             =  "Size";
$strTitle            =  "Page title";
$strSizeAdd          =  "Size of resources loaded together (images, sounds, ...)";
$strPageWeight       =  "Total weight of the page (caching supposed)";
$strRetrievingFlag   =  "URL marked as";
$strLinkType         =  "Link type";
$strLinkResult       =  "Result";
$strHTMLStatement    =  "HTML statement";
$strHTMLAttribute    =  "HTML attribute";
$strAnchor           =  "Anchor";
$strReferer          =  "Referer URL";
$strHopCount         =  "Hop count (distance in clicks from the starting page)";

/* Page Management */
$strRecordFound   =  "Records found:";
$strListPageInfo  =  "List from n. %s to %s out of %s records found";
$strNext          =  "Next";
$strPrevious      =  "Previous";
$strFirst         =  "First";
$strLast          =  "Last";
$strBack          =  "Back";

/* Filters */
$strFilterLinks   =  "Set the filter for the list of links";
$strLike          =  "Like";
$strNotLike       =  "Not like";
$strRegExp        =  "RegExp";
$strNotRegExp     =  "Not RegExp";
$strGt            =  "Greater than";
$strLt            =  "Less than";

/* List of links page */
$strLinkSubmit    =  "Submit";
$strAllLinks      =  "All of them";
$strOK            =  "OK";
$strNotChecked    =  "Not checked";
$strNotRetrieved  =  "Not retrieved";
$strBroken        =  "Broken link";
$strRedirected    =  "Redirected link";
$strAnchorNotFound =  "Anchor not found";
$strNormal        =  "Normal link";
$strDirect        =  "Direct link";
$strRedirection   =  "HTTP Redirection";
$strHelpOnString  =  "You can set patterns to be matched with '$strLike' "
                     . "and '$strNotLike'. The first, gives you back results that "
                     . "match your SQL pattern, whereas the latter gives back those "
                     . "that don't match it. You can use wildcard characters like "
                     . "'%' (any sequence of characters) and '_' (any single "
                     . "character).";
$strHelpOnRegExp  =  "By choosing the '$strRegExp' option, you can use the extended "
                     . "regular expression match. Results that match your expression "
                     . "are given back. In order to obtain those records that "
                     . "don't match your regular expression, use '$strNotRegExp'."; 

/* Show Url page */
$strFilterUrls       =  "Set the filter for the list of URLs";
$strInfoAvailable    =  "Info available";
$strSizeInfo         =  "Info about the size of the resource";
$strRetrievingInfo   =  "Info about the retrieving process";
$strOutgoingLinks    =  "Info about outgoing links";
$strIncomingLinks    =  "Info about incoming links";
$strNotRetrieved     =  "URL not retrieved";
$strShow             =  "Show";
$strAllCTypes        =  "All";
$strAllSCodes        =  "All";

/* Show Link page */
$strReferencingUrl   =  "Referencing URL";
$strReferencedUrl    =  "Referenced URL";
$strLinkIssued       =  "This link has been issued by";
$strOperations       =  "Available operations";
$strOpenThisUrl      =  "Open this URL in a new window";
$strOpenReferencingUrl  =  "Open referencing URL in a new window";
$strOpenReferencedUrl   =  "Open referenced URL in a new window";

/* Database Drop Page */
$strDBDropSure =  "Are you sure you want to drop the database? "
                  . "All data will be lost forever";
$strDBDrop     =  "Yes, I want to drop the database";
$strDBCancel   =  "No, I'd better keep it alive";


/* Errors */
$strErrorNoDBSelected   =  "Error! No database has been selected. Go back.";
$strErrorNoUrlSelected  =  "Error! No Url has been selected. Go back.";
$strErrorMissingInfo    =  "Error! Missing Info. Go back.";

?>
