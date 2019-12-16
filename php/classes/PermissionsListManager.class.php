<?php
  //All the ways to manage list level permissions
  class PermissionsListManager {
    public $_db;

    function __construct($db) {
      $this->_db = $db;
    }

    public function get($list) {
      $statement = $this->_db->prepare("SELECT send FROM default_list WHERE list = :list");
      $statement->bindValue(":list", $list);
      $statement->execute();
      $data = $statement->fetchAll();
      return (isset($data[0])) ? $data[0] : array();
    }

    public function add($list, $send) {
      $statement = $this->_db->prepare("INSERT INTO default_list (list, send) VALUES(:list, :send)");
      $statement->bindValue(":list", $list);
      $statement->bindValue(":send", $send, PDO::PARAM_BOOL);
      $statement->execute();
      return false;
    }

    public function update($list, $send) {
      $statement = $this->_db->prepare("UPDATE default_list SET send = :send WHERE list = :list");
      $statement->bindValue(":send", $send, PDO::PARAM_BOOL);
      $statement->bindValue(":list", $list);
      $statement->execute();
      return false;
    }

    public function delete($list) {
      $statement = $this->_db->prepare("DELETE FROM default_list WHERE list = :list");
      $statement->bindValue(":list", $list);
      $statement->execute();
      return false;
    }
  }
