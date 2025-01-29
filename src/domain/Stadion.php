<?php

namespace ProdajaKarata\domain;

class Stadion {

    private int $id;
    private $name;
    private $info;
    
    //Getters
    public function getId():int {
        return $this->id;
    }

    public function getName():String{
        return $this->name;
    }

    public function getInfo():String{
        return $this->info;
    }

    //Setters
    public function setId(int $id){
        $this->id=$id;
    }

    public function setName(String $name){
        $this->name=$name;
    }

    public function setInfo(String $info){
        $this->info=$info;
    }

    
}