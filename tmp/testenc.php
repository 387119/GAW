<?php
#gaw_logins.txt
#/var/lib/dokuwiki/data/pages
#$str="U2FsdGVkX1+5yiwW2FBaKg0Rn2h39N1efNS+8LY5oAVNcCczx1SNq+C7880qwwUH";
#$x=mcrypt_decrypt(MCRYPT_RIJNDAEL_256,"psw",$str,MCRYPT_MODE_CFB);
#$x=openssl_decrypt($str,"aes-256-cbc",'psw');
$login="387119";
$x=exec ('grep "'.$login.'" /var/lib/dokuwiki/data/pages/gaw_logins.txt | cut -d"|" -f 3 | sed -e "s/<decrypt>\(.*\)<\/decrypt>/\1/" | openssl enc -d -aes-256-cbc -a -k psw');
echo "---".$x."---";
?>
