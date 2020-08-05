<?php

//    include/mysqldb.inc
//    Class definition for interfacing with the MySQL database server
//    Only basic connection features
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
//    $Id: mysqldb.inc,v 1.7 2000/10/16 16:06:57 angusgb Exp $

if ( defined( '__MYSQLDB_H' ) ) return;
define( '__MYSQLDB_H', 1 );

include ("include/db.inc");

class MySQLDB extends DB
{

   // Class attributes

   var $linkid;   // DB Connection ID
   var $result;   // Result of a query
   var $errno;    // Error number
   var $errmsg;   // Error string

   // Constructor
   function MySQLDB($strhostname, $strusername, $strpassword)
   {
      $this->hostname = $strhostname;
      $this->username = $strusername;
      $this->password = $strpassword;
      $linkid = 0;
      $result = 0;

   }

   // Database dropping
   function Drop($DBName)
   {
      if (! $this->result = @mysql_drop_db ($DBName))
      {
         $this->GenerateError(104);
      } else $this->errno = 0;   // Inizializza il codice d'errore
      return $this->errno;
   }

   // Query execution
   function Query($DBName, $StrSQL)
   {
      if ($this->SelectDB($DBName))   // Select the database
         return $this->errno;   // Failed

      if (! $this->result = @mysql_query ($StrSQL))
      {
         $this->GenerateError(103);
      } else $this->errno = 0;   // Inizializza il codice d'errore
      return $this->errno;
   }

   // Function for connecting
   function Connect()
   {

      if (! $this->linkid=@mysql_pconnect ($this->hostname,
         $this->username, $this->password))
      {
         $this->GenerateError(101);
      } else $this->errno = 0;   // Inizializza il codice d'errore
      return $this->errno;

   }

   // Selection of the database
   function SelectDB($DBName)
   {
      if (! $this->linkid )  // Not connected yet
      {
         if ($this->Connect())   // Try to connect
            return $this->errno;   // Failed
      }

      if (! @mysql_select_db ($DBName, $this->linkid))
      {
         $this->GenerateError(102);
      } else $this->errno = 0;   // Inizializza il codice d'errore

      return $this->errno;
   }

   // Generate
   function GenerateError($errno)
   {
      $this->errmsg = mysql_error();
      $this->errno = $errno;
   }

   function FetchObject()
   {
      return mysql_fetch_object($this->result);
   }

   function FetchRow()
   {
      return mysql_fetch_row($this->result);
   }

   function FetchArray()
   {
      return mysql_fetch_array($this->result);
   }

   function Free()
   {
      return mysql_free_result($this->result);
   }

   function NumRows()
   {
      return mysql_num_rows($this->result);
   }


   // This function retrieves the htcheck database on the MySQL server
   function GetHtCheckDBList()
   {

      unset($this->HtDBs);   // initialize the contents

      if (! $this->linkid )  // Not connected yet
      {
         if ($this->Connect())   // Try to connect
            return $this->errno;   // Failed
      }

      if (! $dbtable = @mysql_list_dbs ())
      {
         $this->GenerateError(102);
      }
      else
      {
         $this->errno = 0;   // Inizializza il codice d'errore
         $numdbs = mysql_num_rows($dbtable);

         for ($i=0; $i < $numdbs; $i++)
         {
            // For every database we look if it's a htcheck one
            $dbname= mysql_tablename ($dbtable, $i);
            $found = false;

            if ($this->Query($dbname, "show tables"))
            {
               return $this->errno;   // Failed
            }
            else
            {
               // Fetch every row
               while (!$found && $row = $this->FetchRow())
               {
                  if ($row[0]=="HtmlStatement")
                     $found=true;
               }
            }
            if ($found)
               $this->HtDBs[] = $dbname;
         }
      }

      return $this->errno;

   }


   function CreateHTMLTable($strSQL, $DBName, $Select=false)
   {

      if ($Select && $this->SelectDB($DBName))  // We must select the db first of all
         return -1;  // Error
      
      // Database selected, let's query it
      if ($this->Query($DBName, $strSQL))
         return -2;

      $result=$this->result;
      
      $num=mysql_num_rows($result);
      
      if ($num)
      {
         // Header
         $numfields=mysql_num_fields($result);
         
         echo "<TABLE border=\"1\">\n";
         echo "<TR>\n";
         
         echo " <TH align=\"right\"> N. </TH>\n";
         for ($j=0; $j<$numfields; $j++)
         {
            ?> <TH> <%= mysql_field_name($result, $j) %> </TH>
<?php
         }
         
         echo "</TR>\n";
         
         // Loop
         $i=0;
         
         while ($row = $this->FetchRow())
         {
            $i++;
            echo "<TR>\n";
            echo " <TD> $i </TD>\n";
            
            for ($j=0; $j<$numfields; $j++)
            {
            ?> <TD<%= $options %>> <%= $row[$j] %> </TD>
<?php
            }
            
            echo "</TR>";
         }
      
         echo "</TABLE>";
      }

      $this->Free();

      return $num;      
   }
   
   
}

?>
