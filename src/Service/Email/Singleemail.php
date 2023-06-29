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

  public function sendNotifEmail($recipientName, $recipientEmail, $subject, $title, $content, $emailLink=null)
  {
    $headers = ['Accept' => 'application/json'];
    $sender = array();
    $sender['username'] = $this->params->get('sitename');
    $sender['useremail'] = $this->params->get('emailadmin');

    $recipient = array();
    $recipient['username'] = $recipientName;
    $recipient['useremail'] = $recipientEmail;

    $emailContent = array();
    $emailContent['subject'] = $subject;
    $emailContent['emailtitle'] = $title;
    $emailContent['emailcontent'] = $content;
    $emailContent['linkaction'] = $emailLink;

    $tab = array();
    $tab["clientAuth"] = 'code-client-api';
    $tab["sender"] = $sender;
    $tab["recipient"] = $recipient;
    $tab["emailContent"] = $emailContent;

    $data = json_encode($tab);

    try {

      $response = $this->client->request('POST', $this->params->get('url_single_email'),
                                        [
                                          'headers' => $headers,
                                          'body' => $data
                                        ]
                                      );
      return $response->getContent();
      } catch (TransportExceptionInterface $e) {
          return '{"error": "Unauthorized"}';
    }
  }
}
