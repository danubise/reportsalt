<?php
/**
 * Created by PhpStorm.
 * User: slava
 * Date: 17.10.15
 * Time: 13:26
 */
exec ("su apache -c echo \"Dear Partner!

Please find attached our invoice for the period 01-30 Sep 2015

Please take the confirm.

--

WBR,


Financial Department

Vivaldi Telecom

tel/fax:+78452 674596

Please find attached our invoice.

Period 01-30 Sep 2015.\" | mutt -s \" Invoice from Vivaldi Corporation for the period 01-30 Sep 2015\" slava@markaz.uz -a /var/www/html/report/application/cloud/22/22-1445115313.0703.pdf",$result);


print_r($result);