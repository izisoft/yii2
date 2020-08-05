<?php

//    include/header.inc
//    Global variables declaration
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
//    $Id: header.inc,v 1.7 2000/10/16 16:06:57 angusgb Exp $

if ( defined( '__HEADER_INC' ) ) return;
define( '__HEADER_INC', 1 );

?>

<HTML>
<HEAD>
<TITLE>ht://Check <%= $Organisation %> / <%= $pagetitle %></TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</HEAD>

<BODY bgcolor="#FFFFFF" text="#000000" link="#990000" vlink="#003399" alink="#FF9999">

<TABLE border="0" width="100%" cellpadding="0" cellspacing="0">
<TR>
<TD>
<H4>ht://Check <%= $Organisation %></H4>
</TD>
<TD align="right" valign="top">
<%= $linkbar %>
</TD>
</TR>
</TABLE>

<HR>
