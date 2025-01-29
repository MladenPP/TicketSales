<?php

namespace ProdajaKarata\model;

use ProdajaKarata\domain\Karta;
use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\exceptions\InvalidArgumentException;
use ProdajaKarata\exceptions\InsufficientFundsException;
use ProdajaKarata\model\StadionModel;
use ProdajaKarata\model\AbstractKartaModel;
use ProdajaKarata\model\KorisnikModel;
use ProdajaKarata\model\Inventory;
use PDO;
use DateTime;

class KartaModel extends AbstractModel {
    const CLASSNAME = 'ProdajaKarata\domain\Karta';

    public function get(int $ticketId): Karta {
        $query = 'SELECT * from tickets WHERE id = :ticketId';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['ticketId' => $ticketId]);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte[0];

    }

    public function getBySId(int $stadiumId): array {
        $query = 'SELECT * from tickets WHERE stadiumId = :stadiumId';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['stadiumId' => $stadiumId]);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;

    }

    public function getByTid(int $ticketId): array {
        $query = 'SELECT * from tickets WHERE ticketId = :ticketId';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['ticketId' => $ticketId]);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;

    }

    public function getByTime(String $timee): array {
        $query = 'SELECT * from tickets WHERE `time` LIKE :timee';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['timee' => '%'.$timee.'%']);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;
    }

    public function getByGame(String $game): array {
        $query = 'SELECT * from tickets WHERE game LIKE :game';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['game' => '%'.$game.'%']);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;

    }

    public function getInStock(int $amount):array {
        $query = 'SELECT * from tickets WHERE amount >= :amount';

        $sth = $this->db->prepare($query);
        $sth->execute(['amount' => $amount]);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;
    }

    public function create(int $stadiumId, int $ticketId, String $timee, String $game, int $amount){
        $format = 'Y-m-d H:i:s';
        $dateTime = DateTime::createFromFormat($format, $timee);
        if ($dateTime == false) {
            throw new InvalidArgumentException("$timee nije validno vreme u formatu Y-m-d H:i:s");
        } 
        try{
        $stadiumModel=new StadionModel($this->db);
        $stadium=$stadiumModel->get($stadiumId);}
        catch(NotFoudException $e){
            throw new InvalidArgumentException("stadion sa id = $stadiumId ne postoji u bazi");
        }
        try{
        $kartaModel=new AbstractKartaModel($this->db);
        $karta=$kartaModel->get($ticketId);}
        catch(NotFoudException $e){
            throw new InvalidArgumentException("karta sa id = $ticketId ne postoji u bazi");
        }

        try {
            $this->db->beginTransaction();
            $query = <<<SQL
            INSERT INTO tickets (stadiumId, ticketId, `time`, game, amount )
            VALUES (:stadiumId, :ticketId, :timee, :game, :amount)
            SQL;
    
            $sth = $this->db->prepare($query);
            $sth->bindParam(':stadiumId', $stadiumId, PDO::PARAM_INT);
            $sth->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            $sth->bindParam(':timee', $timee, PDO::PARAM_STR);
            $sth->bindParam(':game', $game, PDO::PARAM_STR);
            $sth->bindParam(':amount', $amount, PDO::PARAM_INT);
    
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

    public function edit(String $field,$edit,int $ticketId){
        $allowedFields = ['time', 'amount'];
        if (!in_array($field, $allowedFields)) {
            throw new InvalidArgumentException("$field nije validno polje");
        } 
        if($field == 'time'){
            $format = 'Y-m-d H:i:s';
            $dateTime = DateTime::createFromFormat($format, $timee);
            if ($dateTime == false) {
                throw new InvalidArgumentException("$timee nije validno vreme u formatu Y-m-d H:i:s");
            }
        }

        $query = <<<SQL
            UPDATE tickets 
            SET $field = :edit
            WHERE id = :id
            SQL;    
        $sth = $this->db->prepare($query);
        $sth->bindParam(':edit', $edit, PDO::PARAM_STR);
        $sth->bindParam(':id', $ticketId, PDO::PARAM_INT);
        if(!$sth->execute()){
            throw new DbException((string) $sth->errorInfo()[2]);
        }

    }

    public function reduceAmount(int $amount,int $ticketId){
        $currAmount=$this->get($ticketId)->getAmount();
        if($currAmount<$amount){throw new InvalidArgumentException("broj karata ne moze biti smanjen ispod 0(broj karata=$currAmount");}
        $newAmount=$currAmount-$amount;
        $this->edit('amount',$newAmount,$ticketId);
    }

    public function buy(int $amount, int $ticketId, int $customerId){
        try{
            $ticket=$this->get($ticketId);}
        catch(NotFoundException $e){
            
            throw new InvalidArgumentException("ne postoji karta sa id= $ticketId");
        }
        $customermodel= new KorisnikModel($this->db);
        try{
            $customer = $customermodel->get($customerId);}
        catch(NotFoundException $e){
            throw new InvalidArgumentException("ne postoji korisnik sa id= $customerId");
        }
        $balance=$customer->getBalance();
        $absticketmodel= new AbstractKartaModel ($this->db);
        $absticket = $absticketmodel->get($ticket->getTicketId());
        $price=$absticket->getPrice();
        if($balance-$price*$amount<0){
            throw new InsufficientFundsException("Nedovoljno novca za kupovinu");}

        $customermodel->reduceadd('-',$price*$amount,$customerId);
        try {
            $this->reduceAmount($amount,$ticketId);
        } catch (\Throwable $th) {
            
            throw new InvalidArgumentException("broj karata ne moze biti smanjen ispod 0");
        }//
        
        $inventorymodel= new InventoryModel($this->db);
        
        try{
            $inventory=$inventorymodel->getByCId($customerId);
        }
        catch(NotFoundException $e){
            
            $inventorymodel->create($customerId,$ticketId,$amount);
            return;
        }
         
        foreach($inventory as $inv){
            if($inv->getTicketId() == $ticketId){
                $inventorymodel->addAmount($amount,$inv->getId());
                return;
            }
        }
        $inventorymodel->create($customerId,$ticketId,$amount);
    }

    public function forprint(int $id){
        $query = <<<SQL
        SELECT t.id,t.game,t.time,ta.orientation AS ticor,ta.price AS ticprice ,s.name AS stadime,t.amount
        FROM tickets AS t
        LEFT JOIN ticketabs AS ta ON t.ticketId = ta.id
        LEFT JOIN stadiums AS s ON t.stadiumId = s.id
        WHERE t.id = :id
        SQL;
        $sth = $this->db->prepare($query);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();
        if ($sth->rowCount() > 0) {
            $results = $sth->fetch(PDO::FETCH_ASSOC);
        }
        else {
            throw new NotFoudnException();
        }
        return $results;
    }

    public function forprintAll(int $id,int $page, int $pageLength){
        $start = $pageLength * ($page - 1);

        $query = <<<SQL
        SELECT t.id,t.game,t.time,ta.orientation AS ticor,ta.price AS ticprice ,s.name AS stadime,t.amount
        FROM tickets AS t
        LEFT JOIN ticketabs AS ta ON t.ticketId = ta.id
        LEFT JOIN stadiums AS s ON t.stadiumId = s.id
        WHERE t.stadiumId=:id
        LIMIT :page, :length
        SQL;
        $sth = $this->db->prepare($query);
        $sth->bindParam('id', $id, PDO::PARAM_INT);
        $sth->bindParam('page', $start, PDO::PARAM_INT);
        $sth->bindParam('length', $pageLength, PDO::PARAM_INT);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}

