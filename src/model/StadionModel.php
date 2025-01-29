<?php

namespace ProdajaKarata\model;

use ProdajaKarata\domain\Stadion;
use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\exceptions\InvalidArgumentException;
use PDO;

class StadionModel extends AbstractModel {
    const CLASSNAME = 'ProdajaKarata\domain\Stadion';

    public function get(int $stadId): Stadion {
        $query = 'SELECT * from stadiums WHERE id = :stadId';
        
        $sth = $this->db->prepare($query);
        $sth->execute(['stadId' => $stadId]);
        $stadioni = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($stadioni)){
            throw new NotFoundException();
        } else return $stadioni[0];

    }

    public function getAll(int $page, int $pageLength): array {
        $start = $pageLength * ($page - 1);

        $query ='SELECT * FROM stadiums LIMIT :page, :length';
        $sth = $this->db->prepare($query);
        $sth->bindParam('page', $start, PDO::PARAM_INT);
        $sth->bindParam('length', $pageLength, PDO::PARAM_INT);
        $sth->execute();
        
        return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    }

    public function getByName(String $name): array {
        $query = 'SELECT * from stadiums WHERE `name` LIKE :stadname';
        $sth = $this->db->prepare($query);
        $sth->execute(['stadname' => $name.'%']);
        $stadioni = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($stadioni)){
            throw new NotFoundException();
        } else return $stadioni; 

    }

    public function create(String $namee, String $info){
        try {
            $checks=$this->getByName($namee);
        } catch (NotFoundException $e) {
            try {
                $this->db->beginTransaction();
                $query = <<<SQL
                INSERT INTO stadiums (`name`, info) 
                VALUES (:namee, :info)
                SQL;
        
                $sth = $this->db->prepare($query);
                $sth->bindParam(':namee', $namee, PDO::PARAM_STR);
                 $sth->bindParam(':info', $info, PDO::PARAM_STR);
                $result = $sth->execute();
        
                if ($result) {
                    $this->db->commit();
                    return;
                } else {
                    $this->db->rollBack();
                    throw new DbException((string) $sth->errorInfo()[2]);
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                throw new DbException($e->getMessage());
            }
        }
        throw new InvalidArgumentException("stadion $namee vec postoji");
    }
}

