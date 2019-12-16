<?php
  //All the ways to manage user level permissions
  class PermissionsManager {
    public $_db;

    function __construct($db) {
      $this->_db = $db;
    }

    public function get($email, $list) {
      $statement = $this->_db->prepare("SELECT admin, goThroughModeration FROM permissions WHERE email = :email AND list = :list");
      $statement->bindValue(":email", $email);
      $statement->bindValue(":list", $list);
      $statement->execute();
      $data = $statement->fetchAll();
      return (isset($data[0])) ? $data[0] : array();
    }

    public function getList($list) {
      $statement = $this->_db->prepare("SELECT email, admin, goThroughModeration FROM permissions WHERE list = :list");
      $statement->bindValue(":list", $list);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_GROUP);
    }

    public function getWhereAdmin($email) {
      $statement = $this->_db->prepare("SELECT list FROM permissions WHERE email = :email AND admin = :list");
      $statement->bindValue(":email", $email);
      $statement->bindValue(":list", true);
      $statement->execute();
      return $statement->fetchAll();
    }

    public function add($email, $list, $isAdmin, $canGoThroughModeration) {
      $statement = $this->_db->prepare("INSERT INTO permissions (email, list, admin, goThroughModeration) VALUES(:email, :list, :admin, :goThroughModeration)");
      $statement->bindValue(":email", $email);
      $statement->bindValue(":list", $list);
      $statement->bindValue(":admin", $isAdmin, PDO::PARAM_BOOL);
      $statement->bindValue(":goThroughModeration", $canGoThroughModeration, PDO::PARAM_BOOL);
      $statement->execute();
      return false;
    }

    public function update($email, $list, $isAdmin, $canGoThroughModeration) {
      $statement = $this->_db->prepare("UPDATE permissions SET admin = :admin, goThroughModeration = :goThroughModeration WHERE email = :email AND list = :list");
      $statement->bindValue(":admin", $isAdmin, PDO::PARAM_BOOL);
      $statement->bindValue(":goThroughModeration", $canGoThroughModeration, PDO::PARAM_BOOL);
      $statement->bindValue(":email", $email);
      $statement->bindValue(":list", $list);
      $statement->execute();
      return false;
    }

    public function delete($email, $list) {
      $statement = $this->_db->prepare("DELETE FROM permissions WHERE email = :email AND list = :list");
      $statement->bindValue(":email", $email);
      $statement->bindValue(":list", $list);
      $statement->execute();
      return false;
    }

    public function deleteList($list) {
      $statement = $this->_db->prepare("DELETE FROM permissions WHERE list = :list");
      $statement->bindValue(":list", $list);
      $statement->execute();
      return false;
    }
  }
