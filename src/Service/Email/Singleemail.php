<?php
//(c) Noel Kenfack   Novembre 2016
namespace App\Service\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Singleemail
{
  private $client;
  private $params;

  public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
  {
      $this->client = $client;
      $this->params = $params;
  }

  
  public function sendNotifEmail($recipientName, $recipientEmail, $subject, $title, $content, $emailLink=null, $senderName=null, $senderEmail = null)
  {
    $headers = ['Accept' => 'application/json', 'Content-Type'=> 'application/json'];
    $urlemail = $this->params->get('url_single_email').'api/messages/email';

    $sender = array();
    if($senderName == null){
      $sender['username'] = $this->params->get('sitename');
    }else{
      $sender['username'] = $senderName;
    }
    
    if($senderEmail == null){
      $sender['email'] = $this->params->get('emailadmin');
    }else{
      $sender['email'] = $senderEmail;
    }

    $recipient = array();
    $recipient['username'] = $recipientName;
    $recipient['email'] = $recipientEmail;

    //$emailContent = array();
    //$emailContent['subject'] = $subject;
    //$emailContent['emailtitle'] = $title;
    //$emailContent['emailcontent'] = $content;
    //$emailContent['linkaction'] = $emailLink;

    $tab = array();
    $tab["clientAuth"] = 'SKJ74PT0B14X09GPPN9K6ZV21M1KCH';
    $tab["sender"] = $sender;
    $tab["recipients"] = array($recipient);
    $tab["emailTitle"] = $title;
    $tab["emailContent"] = $content;
    $tab["userDefaultTemplate"] = 0;
    $tab["emailBoostLink"] = '';

    $data = json_encode($tab);

    try {
      $response = $this->client->request('POST', $urlemail,
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
