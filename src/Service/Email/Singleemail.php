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
    $sender['email'] = $this->params->get('emailadmin');

    $recipient = array();
    $recipient['username'] = $recipientName;
    $recipient['email'] = $recipientEmail;

    $tab = array();
    $tab["clientAuth"] = 'R985MBMPQ2IBCQ1PKJLJ1O1HLKRG90';
    $tab["sender"] = $sender;
    $tab["recipients"] = array($recipient);

    $tab['subject'] = $subject;
    $tab["emailTitle"] = $title;
    $tab["emailContent"] = $content;
    $tab["userDefaultTemplate"] = 0;
    $tab["emailBoostLink"] = $emailLink;;


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
