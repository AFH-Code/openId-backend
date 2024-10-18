<?php
//(c) Noel Kenfack   Novembre 2016
namespace App\Service\Sms;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SingleSmsService
{
  private $client;
  private $params;
  private GeneralServicetext $_generalServicetext;

  public function __construct(HttpClientInterface $client, ParameterBagInterface $params, GeneralServicetext $generalServicetext)
  {
      $this->client = $client;
      $this->params = $params;
      $this->_generalServicetext = $generalServicetext;
  }

  public function sendSms($recipientName, $recipientSms, $content, $senderName=null, $senderSms = null, $extension=null, $operateur=null)
  {
    $headers = ['Accept' => 'application/json', 'Content-Type'=> 'application/json'];
    $urlsms = $this->params->get('url_single_email').'api/messages/sms';

    $sender = array();
    if($senderName == null){
      $sender['username'] = $this->params->get('sitename');
    }else{
      $sender['username'] = $senderName;
    }
    if($senderSms == null){
      $sender['telephone'] = "".$this->params->get('emailadmin');
    }else{
      $sender['telephone'] = "".$senderSms;
    }
    $sender['extension'] = "cm";
    
    $recipient = array();
    $recipient['username'] = $recipientName;
    $recipient['telephone'] = $recipientSms;
    if($extension == null)
    {
        $recipient['extension'] = "cm";
    }else{
        $recipient['extension'] = $extension;
    }

    if($operateur == null)
    {
        $recipient['operateur'] = "om";
    }else{
        $recipient['operateur'] = $operateur;
    }

    $tab = array();
    $tab["clientAuth"] = 'SKJ74PT0B14X09GPPN9K6ZV21M1KCH';
    $tab["sender"] = $sender;
    $tab["recipients"] = array($recipient);
    $tab["smsContent"] = $content;
    $tab["smsBoostLink"] = '';

    $data = json_encode($tab);

    $this->_generalServicetext->setLoggerMethod(__CLASS__.'::'.__FUNCTION__, 'Send Sms '.$data, 'Anonyme');

    try {

      $response = $this->client->request('POST', $urlsms,
                                      [
                                        'headers' => $headers,
                                        'body' => $data,
                                        'verify_peer' => false, 'verify_host' => false
                                      ]
                                    );

      $statusCode = $response->getStatusCode();
      if($statusCode == 401 || $statusCode == 500)
      {
          return '{"error": "Unauthorized"}';
      }else{
          return $response->getContent();
      }
    } catch (TransportExceptionInterface $e) {
          return '{"error": "Unauthorized"}';
    }
  }


  public function sendMultiRecipientsSms($recipientTable, $content, $senderName=null, $senderSms = null, $extension = null)
  {
    $headers = ['Accept' => 'application/json', 'Content-Type'=> 'application/json'];
    $urlsms = $this->params->get('url_single_email').'api/messages/sms';

    $sender = array();
    if($senderName == null){
      $sender['username'] = $this->params->get('sitename');
    }else{
      $sender['username'] = $senderName;
    }
    if($senderSms == null){
      $sender['telephone'] = "687985874";//$this->params->get('emailadmin');
    }else{
      $sender['telephone'] = "687985874";//$senderSms;
    }
    $sender['extension'] = "cm";


    $tab = array();
    $tab["clientAuth"] = 'SKJ74PT0B14X09GPPN9K6ZV21M1KCH';
    $tab["sender"] = $sender;
    $tab["recipients"] = $recipientTable;
    $tab["smsContent"] = $content;
    $tab["smsBoostLink"] = '';

    $data = json_encode($tab);

    $this->_generalServicetext->setLoggerMethod(__CLASS__.'::'.__FUNCTION__, 'Send Sms '.$data, 'Anonyme');

    try {

      $response = $this->client->request('POST', $urlsms,
                                      [
                                        'headers' => $headers,
                                        'body' => $data,
                                        'verify_peer' => false, 'verify_host' => false
                                      ]
                                    );

      $statusCode = $response->getStatusCode();
      if($statusCode == 401 || $statusCode == 500)
      {
          return '{"error": "Unauthorized"}';
      }else{
          return $response->getContent();
      }
    } catch (TransportExceptionInterface $e) {
          return '{"error": "Unauthorized"}';
    }
  }
}