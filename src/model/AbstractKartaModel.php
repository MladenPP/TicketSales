<?php

namespace ProdajaKarata\model;

use ProdajaKarata\domain\AbstractKarta;
use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\exceptions\InvalidArgumentException;
use PDO;

class AbstractKartaModel extends AbstractModel {
    const CLASSNAME = 'ProdajaKarata\domain\AbstractKarta';

    public function get(int $ticketId): AbstractKarta {
        $query = 'SELECT * from ticketabs WHERE id = :ticketId';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['ticketId' => $ticketId]);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte[0];

    }

    public function getAll(): array {
        $query = 'SELECT * from ticketabs';
        
        $sth = $this->db->prepare($query);
        $sth->execute();
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;

    }

    public function getByOrientation(String $orientation): array {
        $allowedOrientation = AbstractKarta::getAllowedOrientation();
        if (!in_array($orientation, $allowedOrientation)) {
            throw new InvalidArgumentException("$orientation nije validna orijentacija");
        } 

        $query = 'SELECT * from ticketabs WHERE orientation = :orientation';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['orientation' => $orientation]);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;
    
    }

    public function getByPrice(float $price, String $sign): array {
        $allowedSigns = ['<', '<=', '>', '>=', '=', '!='];
            if (!in_array($sign, $allowedSigns)) {
                throw new InvalidArgumentException('Nekorektan znak poredjenja');
    }

        $query = 'SELECT * from ticketabs WHERE price'.$sign.':price';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['price' => $price]);
        $karte = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($karte)){
            throw new NotFoundException();
        } else return $karte;

    }

    public function create(String $orientation, Float $price){
        $allowedOrientation = AbstractKarta::getAllowedOrientation();
        if (!in_array($orientation, $allowedOrientation)) {
            throw new InvalidArgumentException("$orientation nije validna orijentacija");
        } 

        try {
            $this->db->beginTransaction();
            $query = <<<SQL
            INSERT INTO ticketabs (orientation, price)
            VALUES (:orientation, :price)
            SQL;
    
            $sth = $this->db->prepare($query);
            $sth->bindParam(':orientation', $orientation, PDO::PARAM_STR);
            $sth->bindParam(':price', $price, PDO::PARAM_STR);
    
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
}

