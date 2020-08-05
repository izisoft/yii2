<?php
namespace izi\mailer;

use PHPMailer\PHPMailer\PHPMailer;
use Yii;
class Mailer extends \PHPMailer\PHPMailer\PHPMailer
{
    
    public function __construct(){
        $this->isSMTP();
        $this->CharSet = "UTF-8";
        $this->SMTPDebug = 0;
        
        $this->Debugoutput = 'html'; 
        
        $this->SMTPAuth = true;
        $this->isHTML(true);
        
        
        /**
         * Get smtp config 
         */
        
        $emails = \app\models\Siteconfigs::getConfigs('EMAILS',false);
        
       

        $smtp = $this->getDefautSmtp();
        
         
        
        $sms = [];
        if(isset($emails['listItem']) && !empty($emails['listItem'])){
            foreach ($emails['listItem'] as $v){
                if( isset($v['is_default']) && $v['is_default'] == 1 && isset($v['is_active']) && $v['is_active'] == 1){
                    $sms = $v;
                    break;
                }
            }
            if(empty($sms)){
//                 $sms = $emails['listItem'][0] ;
            }
            if(!empty($sms))  $smtp = $sms;
        }

        //$this->From =   isset(Yii::$app->cfg->contact['email']) ? Yii::$app->cfg->contact['email'] : 'no-reply@'. DOMAIN;
        
        $this->From = validateEmail($smtp['email']) ? $smtp['email'] : (isset($smtp['from_email']) ? $smtp['from_email'] : 'no-reply@'. DOMAIN);
        
        $this->FromName = isset(Yii::$app->cfg->contact['short_name']) ? Yii::$app->cfg->contact['short_name'] : DOMAIN;
        
        $this->Host = $smtp['host'];
        
         
         
        // use
        // $mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->Port = $smtp['port'];
        //Set the encryption system to use - ssl (deprecated) or tls
        $this->SMTPSecure = $smtp['smtpsecure'];
        //Whether to use SMTP authentication
        
        //Username to use for SMTP authentication - use full email address for gmail
        $this->Username = $smtp['email'];
        //Password to use for SMTP authentication
        $this->Password = dString($smtp['password']);
        
        $this->smtpConnect([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        parent::__construct();
    }
    
    
    public function getDefautSmtp(){
        return [
            'host' => 'email-smtp.us-east-1.amazonaws.com',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
            'email' => 'AKIAZNUBEWD4CW2VZJEY',
            'password' => 'anNqOGFuTnFPRUpFYlZGV1ZrRkpUMUpMZWpaRVVHOHlVekpTZEhocFdsbDBZWEJqV0ZRek1qbERRWEpEZEVSSFRUTTM=',
            'port' => 587, // Port 25 is a very common port too
            'smtpsecure' => 'tls', // It is often used, check your provider or mail server specs,
            'from_email'=>'no-reply@iziweb.net'
        ];
    }
    
    public function validateEmail($email){
        if(filter_var($email, FILTER_VALIDATE_EMAIL) !==false){
            return true;
        }
        return false;
    }
    
    public function sentEmail($params){
        return $this->sendEmail($params);
    }
    
    
    public function sendDirectEmail($params)
    {
        $fromEmail = isset($params['fromEmail']) && $params['fromEmail'] != "" ? $params['fromEmail'] 
        : (isset($params['email']) ? $params['email'] : 'noreply@'. DOMAIN); // replace with your own
        
        $replyTo = isset($params['replyTo']) ? $params['replyTo'] : '';
        
        if (is_array($replyTo)){
            if(isset($replyTo[0]) && $this->validateEmail($replyTo[0])){
                
                $replyTo = $replyTo[0];
                
            }elseif(isset($replyTo['email']) && $this->validateEmail($replyTo['email'])){
                $replyTo = $replyTo['email'];
            }else{
                $email = array_keys($replyTo)[0];
                if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $replyTo = $email;
                }
            }
            
        } 
        
        
        $to = isset($params['to']) ? $params['to'] : '';
        $toEmails = !is_array($to) ? explode(',', str_replace(';', ',', $to)) : $to;
        
        
        
        if(!empty($toEmails)){
            $state = false;
            foreach ($toEmails as $k=>$var){
                
                if(is_array($var)){
                    if(isset($var[0]) && $this->validateEmail($var[0])){
                        $to =  $var[0] ;
                        $state = true;
                    }elseif(isset($var['email']) && $this->validateEmail($var['email'])){
                        $to =  $var['email'];
                        $state = true;
                    }else{
                        $to = array_keys($var)[0] ;
                        $state = true;
                    }
                }elseif($this->validateEmail($var)){
                    $to =  $var ;
                    $state = true;
                }
                
                //
                unset($toEmails[$k]);
                //
                if($state){
                    break;
                }
            }
        }
        
        
        
        
        
        
        $headers = "From: " . strip_tags($fromEmail) . "\r\n";
        if($replyTo != ""){
            $headers .= "Reply-To: ". strip_tags($replyTo) . "\r\n";
        }
        
        $cc = isset($params['cc']) ? $params['cc'] : '';
        
        $ccEmails = !is_array($cc) ? explode(',', str_replace(';', ',', $cc)) : $cc;
        
        if(!empty($ccEmails)){
            foreach ($ccEmails as $email){
                
                if(isset($email[0]) && $this->validateEmail($email[0])){
                    
                    $headers .= "CC: ".$email[0]."\r\n";
                    $state = true;
                }elseif(isset($email['email']) && $this->validateEmail($email['email'])){

                    $headers .= "CC: ".$email['email']."\r\n";
                    $state = true;
                }elseif(is_array($email)){
                    if(filter_var(array_keys($email)[0],FILTER_VALIDATE_EMAIL)){
                        $headers .= "CC: ".array_keys($email)[0]."\r\n";
                    }
                }elseif(filter_var($email,FILTER_VALIDATE_EMAIL)){
                    $headers .= "CC: ".$email."\r\n";
                }
            }
        }
        
        
        //$headers .= "CC: susan@example.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        
        
        return mail($to, $params['subject'], $params['message'], $headers);
        
    }
    
    
    public function sendEmail($o){
        /**
         * 
         * @var Ambiguous $subject
         */
        
        $mailer = new Mailer(true); 
        
        $subject = isset($o['subject']) ? $o['subject'] : '';
        $body = isset($o['body']) ? $o['body'] : '';
        $messageBody = isset($o['messageBody']) ? $o['messageBody'] : $body;
        
        
        $mailer->Subject = $subject;
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mailer->msgHTML($messageBody);
        
        $from = isset($o['from']) ? $o['from'] : $this->From;
        $from = isset($smtp['from_email']) && $smtp['from_email'] != "" ? $smtp['from_email'] : (isset($smtp['email']) ? $smtp['email'] : $from); // replace with your own
        
        $fromEmail = isset($smtp['fromEmail']) && $smtp['fromEmail'] != "" ? $smtp['fromEmail'] : (isset($smtp['email']) ? $smtp['email'] : $from); // replace with your own
        
        
        $fromName = isset($o['fromName']) ? $o['fromName'] : (isset($smtp['from_name']) ? $smtp['from_name'] : $fromEmail); // replace with your own
        $replyTo = isset($o['replyTo']) ? $o['replyTo'] : ''; // replace with your own
        $replyToName = isset($o['replyToName']) ? $o['replyToName'] : ''; // replace with your own
        $templete = isset($o['templete']) ? $o['templete'] : [];
        $to = isset($o['to']) ? $o['to'] : '';
        $toName = isset($o['toName']) ? $o['toName'] : '';
        //
        
        $setFrom = $fromEmail != $fromName ? [$fromEmail => $fromName] : $fromEmail;    
        
        //
        if(is_array($from) && !empty($from)){
            if($this->validateEmail($from[0])){
//                 $mailer->From = $from[0];
            }
            
            if(isset($from[1]) && is_string($from[1])){
                $mailer->FromName = $from[1];
            }
            
        }
         
        if (is_array($replyTo)){
            if(isset($replyTo[0]) && $this->validateEmail($replyTo[0])){ 
                
                $mailer->addReplyTo($replyTo[0],isset($replyTo[1]) ? $replyTo[1] : '');     
                
            }elseif(isset($replyTo['email']) && $this->validateEmail($replyTo['email'])){
                $mailer->addReplyTo($replyTo['email'],isset($replyTo['name']) ? $replyTo['name'] : '');     
            }else{
                $email = array_keys($replyTo)[0];
                if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $mailer->addReplyTo($email,array_keys($var)[1]);
                }
            }
            
        }elseif(filter_var($replyTo, FILTER_VALIDATE_EMAIL) && $this->From != $replyTo){
            $mailer->addReplyTo($replyTo,$replyToName);
        }      
        
        
       
        //
        
           
        
        
        $toEmails = !is_array($to) ? explode(',', str_replace(';', ',', $to)) : $to;
        
        $cc = isset($o['cc']) ? $o['cc'] : '';
        
        $ccEmails = !is_array($cc) ? explode(',', str_replace(';', ',', $cc)) : $cc;
        
        $bcc = isset($o['bcc']) ? $o['bcc'] : '';
        
        $bccEmails = !is_array($bcc) ? explode(',', str_replace(';', ',', $bcc)) : $bcc;
        
        /**
         * $toEmails = [
         *      'email1@domain.com',
         *      'email2@domain.com',
         *      'email3@domain.com'
         * ]
         * 
         * $toEmails = [
         *      ['email1@domain.com'=>'Email 1'],
         *      ['email2@domain.com'=>'Email 2'],
         *      ['email3@domain.com'=>'Email 3']
         * ]
         */
         
        
        
        if(!empty($toEmails)){
            $state = false;
            foreach ($toEmails as $k=>$var){
 
                if(is_array($var)){
                    if(isset($var[0]) && $this->validateEmail($var[0])){
                        $mailer->addAddress($var[0],isset($var[1]) ? $var[1] : $toName);
                        $state = true;
                    }elseif(isset($var['email']) && $this->validateEmail($var['email'])){
                        $mailer->addAddress($var['email'],isset($var['name']) ? $var['name'] : $toName);
                        $state = true;
                    }else{
                        $mailer->addAddress(
                            array_keys($var)[0],
                            array_values($var)[0]
                            );
                        $state = true;
                    }
                }elseif($this->validateEmail($var)){
                    $mailer->addAddress($var,$toName);
                    $state = true;
                }
                
                //
                unset($toEmails[$k]);
                //
                if($state){
                    break;
                }
            }
        }
        
        
        
        /**
         * add Bcc email
         */
        $bccEmails = array_merge($bccEmails, $toEmails);
        
        if(!empty($bccEmails)){
            foreach ($bccEmails as $email){

                if(isset($email[0]) && $this->validateEmail($email[0])){
                    $mailer->addBCC($email[0],isset($email[1]) ? $email[1] : '');
                    $state = true;
                }elseif(isset($email['email']) && $this->validateEmail($email['email'])){
                    $mailer->addBCC($email['email'],isset($email['name']) ? $email['name'] : '');
                    $state = true;
                }elseif(is_array($email)){
                    if(filter_var(array_keys($email)[0],FILTER_VALIDATE_EMAIL)){
                        $mailer->addBCC(array_keys($email)[0],array_values($email)[0]);
                    }
                }elseif(filter_var($email,FILTER_VALIDATE_EMAIL)){
                    $mailer->addBCC($email);
                }
            }
        }
        
        
        
        
        /**
         * add cc email
         */        
        
        if(!empty($ccEmails)){
            foreach ($ccEmails as $email){
                
                if(isset($email[0]) && $this->validateEmail($email[0])){
                    $mailer->addCC($email[0],isset($email[1]) ? $email[1] : '');
                    $state = true;
                }elseif(isset($email['email']) && $this->validateEmail($email['email'])){
                    $mailer->addCC($email['email'],isset($email['name']) ? $email['name'] : '');
                    $state = true;
                }elseif(is_array($email)){
                    if(filter_var(array_keys($email)[0],FILTER_VALIDATE_EMAIL)){
                        $mailer->addCC(array_keys($email)[0],array_values($email)[0]);
                    }
                }elseif(filter_var($email,FILTER_VALIDATE_EMAIL)){
                    $mailer->addCC($email);
                }
            }
        }
          
        if(!$mailer->send()) {


            
            
            $fp = Yii::getAlias('@runtime/logs/mailer.log');
            
            writeFile($fp, $mailer->ErrorInfo . '
'.$mailer->Username.'

' . $messageBody);
            
            $params = $o;
            
            $params['subject'] = $subject;
            $params['message'] = $messageBody;
            
            
            
            return $this->sendDirectEmail($params);
            
            return false;
        } else {
            return true;
        }
        
//         return $mailer->send();
        
    }
}