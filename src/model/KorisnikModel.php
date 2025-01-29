<?php

namespace ProdajaKarata\model;

use ProdajaKarata\domain\Korisnik;
use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\exceptions\InvalidArgumentException;
use ProdajaKarata\model\InventoryModel;
use PDO;

class KorisnikModel extends AbstractModel {
    const CLASSNAME = 'ProdajaKarata\domain\Korisnik';

    public function get(int $userId): Korisnik {
        $query = 'SELECT * from customers WHERE id = :userId';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['userId' => $userId]);
        $korisnici = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($korisnici)){
            throw new NotFoundException();
        } else return $korisnici[0];

    }

    public function getAll(): array {
        $query ='SELECT * FROM customers';
        $sth = $this->db->prepare($query);
        $sth->execute();
        
        return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    }

    public function getAllS(int $page, int $pageLength): array {
        $start = $pageLength * ($page - 1);

        $query ='SELECT * FROM customers LIMIT :page, :length';
        $sth = $this->db->prepare($query);
        $sth->bindParam('page', $start, PDO::PARAM_INT);
        $sth->bindParam('length', $pageLength, PDO::PARAM_INT);
        $sth->execute();
        
        return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    }

    public function getByEmail(String $email): Korisnik {
        $query = 'SELECT * FROM customers WHERE email = :email';
        $sth = $this->db->prepare($query);
        $sth->execute(['email' => $email]);
        $korisnici = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($korisnici)){
            throw new NotFoundException();
        } else return $korisnici[0];

    }

    public function getByName(String $name): array {
        $query = 'SELECT * from customers WHERE `name` LIKE :username';
        $sth = $this->db->prepare($query);
        $sth->execute(['username' => '%'.$name.'%']);
        $korisnici = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($korisnici)){
            throw new NotFoundException();
        } else return $korisnici; 

    }

    public function getBySurname(String $surname): array {
        $query = 'SELECT * from customers WHERE surname LIKE :surname';
        $sth = $this->db->prepare($query);
        $sth->execute(['surname' => '%'.$surname.'%']);
        $korisnici = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($korisnici)){
            throw new NotFoundException();
        } else return $korisnici;

    }

    public function getByClass(int $class): array {
        $allowedClass = Korisnik::getAllowedClass();
        if (!in_array($class, $allowedClass)) {
            throw new InvalidArgumentException("$class nije validna klasa");
        }
        $query = 'SELECT * from customers WHERE class = :class';
        $sth = $this->db->prepare($query);
        $sth->execute(['class' => $class]);
        $korisnici = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($korisnici)){
            throw new NotFoundException();
        } else return $korisnici;

    }

    public function getByBal(float $balance, String $sign): array {
        $allowedSigns = ['<', '<=', '>', '>=', '=', '!='];
            if (!in_array($sign, $allowedSigns)) {
                throw new InvalidArgumentException('Nekorektan znak poredjenja');
    }

        $query = 'SELECT * from customers WHERE balance '.$sign.' :balance';
        $sth = $this->db->prepare($query);
        $sth->execute(['balance' => $balance]);
        $korisnici = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($korisnici)){
            throw new NotFoundException();
        } else return $korisnici;

    }

    public function create(String $namee, String $surname, String $email, String $class, Float $balance,String $pass){
        $allowedClass = Korisnik::getAllowedClass();
        if (!in_array($class, $allowedClass)) {
            throw new InvalidArgumentException("$class nije validna klasa");
        }
        $encpass = md5($pass);

        try {
            $this->db->beginTransaction();
            $query = <<<SQL
            INSERT INTO customers (`name`, surname, email, class, balance, `password` )
            VALUES (:namee, :surname, :email, :class, :balance, :pass)
            SQL;
    
            $sth = $this->db->prepare($query);
            $sth->bindParam(':namee', $namee, PDO::PARAM_STR);
            $sth->bindParam(':surname', $surname, PDO::PARAM_STR);
            $sth->bindParam(':email', $email, PDO::PARAM_STR);
            $sth->bindParam(':class', $class, PDO::PARAM_STR);
            $sth->bindParam(':balance', $balance, PDO::PARAM_STR);
            $sth->bindParam(':pass', $encpass, PDO::PARAM_STR);
    
            if ($sth->execute()) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
                throw new DbException((string) $sth->errorInfo()[2]);
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new DbException($e->getMessage());
        }
    }

    public function edit(String $field, $edit, Int $customerId){
        $allowedFields = ['name', 'surname', 'email', 'class', 'balance','password'];
        $allowedClass = Korisnik::getAllowedClass();
        if (!in_array($field, $allowedFields)) {
            throw new InvalidArgumentException("$field nije validno polje");
        } elseif($field == 'balance' && is_string($edit)){
            throw new InvalidArgumentException("$edit mora biti broj");
        }
        if ($field=='class' && !in_array($edit, $allowedClass)) {
            throw new InvalidArgumentException("$edit nije validna klasa");
        }
        if ($field=='password') {
            $edit=md5($edit);
        }

        $query = <<<SQL
            UPDATE customers 
            SET $field = :edit
            WHERE id = :id
            SQL;    
        $sth = $this->db->prepare($query);
        $sth->bindParam(':edit', $edit, PDO::PARAM_STR);
        $sth->bindParam(':id', $customerId, PDO::PARAM_INT);
        if(!$sth->execute()){
            throw new DbException((string) $sth->errorInfo()[2]);
        }
    }

    public function reduceadd(String $sign,float $amount,int $customerId){
        try{
            $korisnik=$this->get($customerId);}
        catch(NotFoundException $e){ 
            throw new InvalidArgumentException("$customerId ne postoji u bazi");}

        if($sign == '+'){
            $newBalance=$korisnik->getBalance()+$amount;
            $this->edit('balance',$newBalance,$customerId);
        }elseif($sign == '-'){
            $balance=$korisnik->getBalance();
            if($balance<$amount){throw new InvalidArgumentException("Balans ne moze biti smanjen ispod 0(balans=$balance");}
            $newBalance=$balance-$amount;
            $this->edit('balance',$newBalance,$customerId);
        }else { throw new InvalidArgumentException("$sign nije validan znak(+,-)");}
    }

    public function delete(int $id){
        try {
            $this->get($id);
        } catch (\Throwable $th) {
           throw new NotFoundException();
        }
        $imodel= new InventoryModel($this->db);
        $imodel->delete($id);
        $query = "DELETE FROM customers WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

