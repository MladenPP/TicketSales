<?php

namespace ProdajaKarata\model;

use ProdajaKarata\domain\Inventory;
use ProdajaKarata\domain\Korisnik;
use ProdajaKarata\model\KartaModel;
use ProdajaKarata\model\KorisnikModel;
use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\exceptions\InvalidArgumentException;
use PDO;

class InventoryModel extends AbstractModel {
    const CLASSNAME = 'ProdajaKarata\domain\Inventory';

    public function get(int $invId): Inventory{
        $query = 'SELECT * from customerhasticket WHERE id = :invId';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['invId' => $invId]);
        $inventari = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($inventari)){
            throw new NotFoundException();
        } else return $inventari[0];

    }

    public function getByCId(int $customerId): array {
        $query = 'SELECT * from customerhasticket WHERE customerId = :customerId';
        $sth = $this->db->prepare($query);
        $sth->execute(['customerId' => $customerId]);
        $inventari = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($inventari)){
            throw new NotFoundException();
        } else return $inventari; 

    }

    public function getByTId(int $ticketId): array {
        $query = 'SELECT * from customerhasticket WHERE ticketId = :ticketId';
        $sth = $this->db->prepare($query);
        $sth->execute(['ticketId' => $ticketId]);
        $inventari = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($inventari)){
            throw new NotFoundException();
        } else return $inventari; 

    }

    public function create(int $customerId,int $ticketId,int $amount=1){
        
        if($amount<=0){throw new InvalidArgumentException("kolicina ne moze biti manja ili jednaka od 0");}
        
        try{
        $customerModel=new KorisnikModel($this->db);
        $korisnik=$customerModel->get($customerId);}
        catch(NotFoudException $e){
            throw new InvalidArgumentException("korisnik sa id = $customerId ne postoji u bazi");
        }
        try{
            
        $kartaModel=new KartaModel($this->db);
        $karta=$kartaModel->get($ticketId);
        }
        
        catch(NotFoudException $e){
            throw new InvalidArgumentException("karta sa id = $ticketId ne postoji u bazi");
        }


        try {
            $this->db->beginTransaction();
            $query = <<<SQL
            INSERT INTO customerhasticket (customerId, ticketId, amount)
            VALUES (:customerId, :ticketId, :amount)
            SQL;
    
            $sth = $this->db->prepare($query);
            $sth->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            $sth->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
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

    public function remove(int $id){
        try{$this->get($id);}
        catch(NotFoundException $e){
            throw new InvalidArgumentException("korisnik sa id = $id ne postoji u bazi");
        }
        $query = "DELETE FROM customerhasticket WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

    }

    public function addAmount(int $amount,int $id){
        $inventory=$this->get($id);
        $newAmount=$inventory->getAmount()+$amount;
       $query = <<<SQL
            UPDATE customerhasticket 
            SET amount = :amount
            WHERE id = :id
            SQL;    
        $sth = $this->db->prepare($query);
        $sth->bindParam(':amount', $newAmount, PDO::PARAM_INT);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();
    }

    public function forprint(int $id){

        $query = <<<SQL
        SELECT c.name AS ime,c.surname AS prezime,t.game AS game,t.time AS time,cht.amount,ta.orientation
        AS orientation,ta.price AS price,s.name AS stadiumName
        FROM customerhasticket AS cht
        LEFT JOIN customers AS c ON cht.customerId = c.id
        LEFT JOIN tickets AS t ON cht.ticketId = t.id
        LEFT JOIN ticketabs AS ta ON t.ticketId = ta.id
        LEFT JOIN stadiums AS s ON t.stadiumId = s.id
        WHERE c.id = :id 
        SQL;
        $sth = $this->db->prepare($query);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();
        if ($sth->rowCount() > 0) {
            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
            throw new NotFoudnException();
        }
        return $results;
    }

    public function delete(int $cusid){
        $query = "DELETE FROM customerhasticket WHERE customerId = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $cusid, PDO::PARAM_INT);
        $stmt->execute();
    }
}

