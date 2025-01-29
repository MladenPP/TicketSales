<?php

namespace ProdajaKarata\domain;

use Exception;

class AbstractKarta {

    private int $id;
    private $orientation;
    private $price;

     //Getters

    public static function getAllowedOrientation(){
        $allowedOrientation=['Sever','Jug','Istok','Zapad','VIP'];
        return $allowedOrientation;
    }
     
    public function getId():int {
        return $this->id;
    }

    public function getOrientation():String{
        return $this->orientation;
    }

    public function getPrice():float{
        return $this->price;
    }

    //Setters
    public function setId(int $id){
        $this->id=$id;
    }

    public function setOrientation(String $orientation){
        $allowedOrientation = AbstractKarta::getAllowedOrientation();
        if (!in_array($orientation, $allowedOrientation)) {
            throw new InvalidArgumentException("$orientation nije validna orijentacija");
        }
        else $this->orientation=$orientation;
    }

    public function setPrice(String $price){
        $this->price=$price;
    }

}