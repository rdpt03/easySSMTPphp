<?php
namespace App\Service;
/*

        Created by Rafael Da Silva
        01/02/2024
        TODO


*/
class SSMTP
{
    private $to;
    private $subject;
    private $messageHTML;
    private $messageText;
    private $attachmentPath;
    private $from;

    /*public function __construct($to, $subject, $messageHTML, $messageText, $attachmentPath, $from)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->messageHTML = $messageHTML;
        $this->messageText = $messageText;
        $this->attachmentPath = $attachmentPath;
        $this->from = $from;
    }*/
    public function __construct()
    {
        $this->attachmentPath=[];
        $this->to = "";
        $this->subject = "";
        $this->messageHTML = "";
        $this->messageText = "";
        $this->from = "";
    }

    public function setTo(string $to){
        $this->to = $to;
    }
    
    public function setSubject(string $subject){
        $this->subject = $subject;
    }

    public function setMessageHTML(string $messageHTML){
        $this->messageHTML = $messageHTML;
    }

    public function setMessageText(string $messageText){
        $this->messageText = $messageText;
    }

    public function addAttachmentPath(string $attachmentPath){
        array_push($this->attachmentPath,$attachmentPath);
    }

    public function setFrom(string $from){
        $this->from = $from;
    }

    public function send()
    {
        //barrier
        $boundary = md5(time());
        //header
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: multipart/mixed; boundary="' . $boundary . '"' . "\r\n";
        $headers .= 'From: ' . $this->from . "\r\n" .
            'Reply-To: ' . $this->from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        //body
        $body = $this->buildEmailBody($boundary);

        try {
            $success = mail($this->to, $this->subject, $body, $headers, "-f {$this->from}");

            if ($success) {
                echo 'Mensagem enviada com sucesso';
            } else {
                throw new Exception('Falha ao enviar a mensagem.');
            }
        } catch (Exception $e) {
            echo "A mensagem não pôde ser enviada. Erro: {$e->getMessage()}";
        }
    }

    private function buildEmailBody($boundary)
    {
        
        $body = "--$boundary\r\n";
        $body .= 'Content-type: multipart/alternative; boundary="' . $boundary . '_alt"' . "\r\n\r\n";

        //textMessage
        $body .= "--" . $boundary . "_alt\r\n";
        $body .= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
        $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
        $body .= $this->messageText . "\r\n\r\n";
        //htmlMessage
        $body .= "--" . $boundary . "_alt\r\n";
        $body .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $body .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
        $body .= $this->messageHTML . "\r\n\r\n";
        $body .= "--" . $boundary . "_alt--\r\n";
        
        //fichiers
        if (!empty($this->attachmentPath)) {
            foreach($this->attachmentPath as $filePath){
                $attachmentContent = file_get_contents($filePath);

                $body .= "--$boundary\r\n";
                $body .= 'Content-type: application/octet-stream; name="' . basename($filePath) . '"' . "\r\n";
                $body .= 'Content-Transfer-Encoding: base64' . "\r\n";
                $body .= 'Content-Disposition: attachment; filename="' . basename($filePath) . '"' . "\r\n\r\n";
                $body .= chunk_split(base64_encode($attachmentContent)) . "\r\n";
                
            }
        }
        $body .= "--$boundary--\r\n";


        return $body;
    }
}
?>