<?php
class SympaManager {
  public function __construct ($wsdl, $username = '', $password = '') {
    $this->wsdl = $wsdl;
    $this->client = new SoapClient($this->wsdl);
    $this->username = $username;
    $this->password = $password;
  }

  //Adds a user to a list
  //if $quiet, doesn't send welcome file
  public function add ($list, $mail, $quiet, $ownerMail = false) {
    return $this->client->authenticateRemoteAppAndRun(
      $this->username, $this->password, "USER_EMAIL=$ownerMail",
      'add',
      array($list, $mail, "", $quiet)
    );
  }

  //Deletes a user from a list
  //if $quiet, doesn't send quit notification
  public function del ($list, $mail, $quiet, $ownerMail = false) {
    return $this->client->authenticateRemoteAppAndRun(
      $this->username, $this->password, "USER_EMAIL=$ownerMail",
      'del',
      array($list, $mail, $quiet)
    );
  }

  public function which ($mail = false) {
    $SoapAnswer = $this->client->authenticateRemoteAppAndRun($this->username, $this->password, "USER_EMAIL=$mail", 'which', null);
    $i = 0;
    $lists = [];
    foreach($SoapAnswer as $listString) {
      $listArray = explode(';', $listString);
      foreach ($listArray as $listItem) {
        $listInfo = explode('=', $listItem, 2);
        $lists[$i][$listInfo[0]] = $listInfo[1];
        }
      $i++;
    }
    return $lists;
  }

  public function review ($list, $mail = false) {
    if(preg_match("/(-tous@)/", $list))
      return false;
    return $this->client->authenticateRemoteAppAndRun($this->username, $this->password, "USER_EMAIL=$mail", 'review', array($list));
  }

  public function createList ($list, $mail = false) {
    return $this->client->authenticateRemoteAppAndRun($this->username, $this->password, "USER_EMAIL=$mail", 'createList', $list);
  }

  public function closeList($list, $mail = false) {
    return $this->client->authenticateRemoteAppAndRun($this->username, $this->password, "USER_EMAIL=$mail", 'closeList', array($list));
  }

  public function lists($mail = false) {
    $lists = $this->client->authenticateRemoteAppAndRun($this->username, $this->password, "USER_EMAIL=$mail", 'complexWhich', null);

    foreach ($lists as $key => $list) { //Remove every list that ends with "tous" as we don't want to show them to the user
      if(preg_match("/(-tous@)/", $list->listAddress))
        unset($lists[$key]);
    }
    return $lists;
  }

  public function info($list, $mail = false) {
    if(preg_match("/(-tous@)/", $list))
      return false;
    return $this->client->authenticateRemoteAppAndRun($this->username, $this->password, "USER_EMAIL=$mail", 'info', array($list));
  }

  //SOAP
  private $client;
  public $wsdl;

  //authenticateRemoteAppAndRun
  public $username;
  public $password;

  //Proxy variables
  //public $USER_EMAIL;
}
?>
