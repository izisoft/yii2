<?php

//    include/db.inc
//    "Abstract" class for interfacing with databases
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
//    $Id: db.inc,v 1.3 2000/10/16 16:06:57 angusgb Exp $

if ( defined( '__DB_H' ) ) return;
define( '__DB_H', 1 );

class DB
{

   // Class attributes

   var $hostname; // Host to connect to
   var $username; // User name of the connection
   var $password; // Password of the user
   var $HtDBs;    // Variable containing the updated list of the htcheck databases
   var $Info;     // General info (array)

   // Get general crawling info
   function GetGeneralInfo($DBName)
   {
      // Select the Database
      if ($this->SelectDB)
         return -1;
         
      // Array of table names
      $tblnames=array("Schedule", "HtmlAttribute", "HtmlStatement",
         "Link", "Server", "Url");

      $num = count($tblnames);

      // Store all the tables record numbers               
      for ($i=0; $i < $num; $i++)
      {
         $strSQL="SELECT count(*) from " . $tblnames[$i];
         $numsched=$this->CountEntries($strSQL,$DBName);
      
         if ( $numsched <0 ) return $numsched;
         else $this->Info[$tblnames[$i]]=$numsched;
      }
      
   }
   
   function CountEntries ($strSQL, $DBName, $Select=false)
   {
      if ($Select && $this->SelectDB($DBName))  // We must select the db first of all
         return -1;  // Error
      
      // Database selected, let's query it
      if ($this->Query($DBName, $strSQL))
         return -2;
      
      // Let's fetch the row
      if ($row = $this->FetchRow())
         $total = $row[0];
      else $total = -3;
      
      return $total;
      
   }
   
}

?>
