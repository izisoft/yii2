<?php

//    include/italian.inc
//    Italian sentences and messages
//
//    Part of the ht://Check package
//
//    Copyright (c) 1999-2000 Gabriele Bartolini - Prato - Italy
//    Some portions Copyright (c) 1995-2000 The ht://Dig Group
//
//    For copyright details, see the file COPYING in your distribution
//    or the GNU General Public License version 2 or later 
//    <http://www.gnu.org/copyleft/gpl.html>
//
//    $Id: italian.inc,v 1.12 2000/09/15 10:55:15 angusgb Exp $

if ( defined( '__ITALIAN_INC' ) ) return;
define( '__ITALIAN_INC', 1 );

/* Main Page */
$strMainPage   =  "Home Page";
$strNumDBs     =  "\$numdbs database di ht://Check sono stati trovati su \$hostname";
$strNoDBs      =  "Mi dispiace. Nessun database di ht://Check è stato trovato su \$hostname";
$strChooseaDB  =  "Scelta";
$strSelectaDB  =  "Scegli un database";

/* Main page of a database */

$strDBSelected =  "Database '<B>\$dbname</B>' selezionato";
$strGeneralInfo   = "Informazioni generali";
$strServersEncountered  =  "Server visitati";
$strScheduledURLS =  "URL trovati (sia visitati che scartati)";
$strRetrievedURLS =  "URL effettivamente recuperati";
$strHTMLStatements   =  "Istruzioni HTML";
$strHTMLAttributes   =  "Attributi HTML";
$strLinks      =  "Link";
$strShowListUrls        =  "Mostra la lista completa degli URL trovati";
$strShowListUrlsSeen    =  "Mostra la lista degli URL recuperati";
$strShowNotFoundUrls    =  "Mostra la lista degli URL non trovati (Codice di stato 404)";
$strShowListLinks       =  "Mostra la lista dei link";
$strShowListUrlsFltr    =  "Mostra la lista degli URL recuperati"
   . " (con possibilità di filtro)";

$strHTTPRequestsResults    =  "Riepilogo dei risulati delle richieste HTTP";
$strServersSeenOrdIDServer =  "Riepilogo dei server visitati (ordinati per richieste)";
$strConnectionResults      =  "Riepilogo dei risultati di connessione";
$strUser    =  "Utente MySQL che ha eseguito la scansione";
$strStartTime  = "Orario di inizio di htcheck";
$strEndTime  = "Orario di inizio di fine";

/* Sections */
$strListofUrls =  "Lista degli URL recuperati";
$strListofAllUrls =  "Lista di tutti gli URL trovati";
$strListofLinks =  "Lista dei link";
$strShowUrl    =  "Informazioni sull'URL";
$strShowLink   =  "Informazioni sul link";
$strDBHome =  "Home Page '<B>\$dbname</B>'";
$strDBDeletion =  "Cancellazione Database";
$strDBDeleted  =  "Database '<B>\$dbname</B>' cancellato";

/* General */

$strSessionExpired   =  "Mi dispiace. Sessione scaduta. Torna all'inizio.";
$strUrl              =  "URL";
$strRequests         =  "Richieste";
$strProtocol         =  "Protocollo";
$strConnectionStatus =  "Stato della connessione";
$strContentType      =  "Content type";
$strTransferEncoding =  "Codifica di trasferimento";
$strStatusCode       =  "Codice di stato";
$strReasonPhrase     =  "Risultato";
$strNumber           =  "Numero";
$strNoOccurrencies   =  "Nessun elemento trovato";
$strWebServerInfo    =  "Informazioni sul server web";
$strLastModified     =  "Data dell'ultima modifica";
$strLastAccess       =  "Data dell'ultimo accesso";
$strLocation         =  "URL redirottato";
$strSize             =  "Dimensione";
$strTitle            =  "Titolo della pagina";
$strSizeAdd          =  "Dimensione aggiuntiva (compreso immagini, ...)";
$strPageWeight       =  "Dimensione complessiva della pagina (supponendo utilizzo cache)";
$strRetrievingFlag   =  "URL contrassegnato come";
$strLinkType         =  "Tipo di link (normale, diretto o redirezione HTTP)";
$strLinkResult       =  "Risultato del Link";
$strHTMLStatement    =  "Istruzione HTML";
$strHTMLAttribute    =  "Attributo HTML";
$strAnchor           =  "Ancora";
$strReferer          =  "URL richiamante";
$strHopCount         =  "Distanza in click dalla pagina iniziale";

/* Page Management */
$strRecordFound   =  "Elementi trovati:";
$strListPageInfo  =  "Lista dal num. %s al %s su %s elementi trovati";
$strNext          =  "Prossimo";
$strPrevious      =  "Precedente";
$strFirst         =  "Inizio";
$strLast          =  "Fine";
$strBack          =  "Indietro";


/* Filters */
$strFilterLinks   =  "Imposta il filtro per la ricerca dei link";
$strLike          =  "Uguale a";
$strNotLike       =  "Diverso da";
$strRegExp        =  "Espr. Reg.";
$strNotRegExp     =  "Non Espr. Reg.";
$strLinkSubmit    =  "Interroga il database";
$strGt            =  "Maggiore di";
$strLt            =  "Minore di";

/* List of links page */
$strAllLinks      =  "Tutti";
$strOK            =  "OK";
$strNotChecked    =  "Non controllato";
$strNotRetrieved  =  "Non recuperato";
$strBroken        =  "Link Rotto";
$strRedirected    =  "Link Redirottato";
$strAnchorNotFound =  "Ancora non trovata";
$strNormal        =  "Normale";
$strDirect        =  "Diretto";
$strRedirection   =  "Redirezione HTTP";
$strHelpOnString  =  "E' possibile specificare stringhe di caratteri da ricercare "
                     . "attraverso '$strLike' e '$strNotLike'. La prima "
                     . "restituisce come risultato quei record che soddisfano "
                     . "il criterio di ricerca impostato nel pattern SQL, "
                     . "mentre restituisce il contrario. "
                     . "E' possibile utilizzare caratteri speciali come "
                     . "'%' (qualsiasi sequenza di caratteri) e '_' (qualsiasi "
                     . "singolo carattere).";
$strHelpOnRegExp  =  "Scegliendo '$strRegExp', è possibile utilizzare le "
                     . "espressioni regolari nelle interrogazioni. "
                     . "Vengono restituiti i record che soddisfano i criteri "
                     . "di ricerca impostati. Per ottenere l'inverso, "
                     . "utilizzare '$strNotRegExp'."; 

/* Show Url page */
$strFilterUrls       =  "Imposta il filtro per la ricerca degli URL";
$strInfoAvailable    =  "Informazioni disponibili";
$strSizeInfo         =  "Informazioni riguardanti le dimensioni della risorsa";
$strRetrievingInfo   =  "Informazioni sul processo di recupero della risorsa";
$strOutgoingLinks    =  "Informazioni riguardanti i link 'in uscita' (da questa risorsa)";
$strIncomingLinks    =  "Informazioni riguardanti i link 'in entrata' (verso questa risorsa)";
$strNotRetrieved     =  "URL non recuperato";
$strShow             =  "Visualizza";
$strAllCTypes        =  "Tutti";
$strAllSCodes        =  "Tutti";
/* Show Link page */
$strReferencingUrl   =  "URL richiamante";
$strReferencedUrl    =  "URL richiamato";
$strLinkIssued       =  "Questo link è stato creato da";
$strOperations       =  "Operazioni disponibili";
$strOpenThisUrl      =  "Apri l'URL in un'altra finestra";
$strOpenReferencingUrl  =  "Apri l'URL richiamante in un'altra finestra";
$strOpenReferencedUrl   =  "Apri l'URL richiamato in un'altra finestra";

/* Database Drop Page */
$strDBDropSure =  "Sei sicuro vuoi cancellare il database? "
                  . "Tutti i dati verranno persi per sempre.";
$strDBDrop     =  "Sì, voglio eliminare il database";
$strDBCancel   =  "No, ci ho ripensato ...";


/* Errors */
$strErrorNoDBSelected   =  "Errore! Nessun database è stato selezionato. Torna indietro.";
$strErrorNoUrlSelected  =  "Errore! Nessun URL è stato selezionato. Torna indietro.";
$strErrorMissingInfo    =  "Errore! Informazioni mancanti. Torna indietro.";

?>
