<?php

namespace ProdajaKarata\model;

use ProdajaKarata\domain\Support;
use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\exceptions\InvalidArgumentException;
use PDO;

class SupportModel extends AbstractModel {
    const CLASSNAME = 'ProdajaKarata\domain\Support';

    public function getAll():array{
        $query = <<<SQL
            SELECT * 
            FROM support
            WHERE answered = 0
            SQL;
        $sth = $this->db->prepare($query);
        $sth->execute();
        
        return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    }

    public function getAllS(int $page=1,int $pageLength):array{
        $start = $pageLength * ($page - 1);

        $query = <<<SQL
            SELECT * 
            FROM support
            WHERE answered = 0
            LIMIT :page, :length
            SQL;
        $sth = $this->db->prepare($query);
        $sth->bindParam(':page', $start, PDO::PARAM_INT);
        $sth->bindParam(':length', $pageLength, PDO::PARAM_INT);
        $sth->execute();
        
        return $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);
    }

    public function create($senderid,$message,){
        try {
            $this->db->beginTransaction();
            $query = <<<SQL
            INSERT INTO support (senderId, `message`,answered)
            VALUES (:id, :msg, 0)
            SQL;
    
            $sth = $this->db->prepare($query);
            $sth->bindParam(':id', $senderid, PDO::PARAM_INT);
            $sth->bindParam(':msg', $message, PDO::PARAM_STR);
    
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
    public function reply($id,$reply){
        try {
            $this->db->beginTransaction();
            $query = <<<SQL
            UPDATE support 
            SET reply = :reply , answered = 1
            WHERE id = :id
            SQL;
    
            $sth = $this->db->prepare($query);
            $sth->bindParam(':reply', $reply, PDO::PARAM_STR);
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
    
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

    public function getAllwithId($id):array{
        $query = <<<SQL
            SELECT * 
            FROM support
            WHERE answered = 1 
            AND senderId = :id
            SQL;
        $sth = $this->db->prepare($query);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();
        $odg = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($odg)){
            throw new NotFoundException();
        } else return $odg;
        
        
    }

    public function getAllSwithId(int $page=1,int $pageLength,$id):array{
        $start = $pageLength * ($page - 1);

        $query = <<<SQL
            SELECT * 
            FROM support
            WHERE answered = 1
            AND senderId = :id
            LIMIT :page, :length
            SQL;
        $sth = $this->db->prepare($query);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->bindParam(':page', $start, PDO::PARAM_INT);
        $sth->bindParam(':length', $pageLength, PDO::PARAM_INT);
        $sth->execute();
        $odg = $sth->fetchAll(PDO::FETCH_CLASS, self::CLASSNAME);

        if(empty($odg)){
            throw new NotFoundException();
        } else return $odg;
    }
}
    