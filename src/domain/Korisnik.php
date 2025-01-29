<?php

namespace ProdajaKarata\domain;

use Exception;

class Korisnik {

    private $id;
    private $name;
    private $surname;
    private $email;
    private $class;
    private $balance;
    private $password;

    //Getters
    public static function getAllowedClass(){
        $allowedClass = ['Korisnik', 'Menadzer', 'Admin'];
        return $allowedClass;
    }

    public function getId():int {
        return $this->id;
    }

    public function getName():String{
        return $this->name;
    }

    public function getSurname():String{
        return $this->surname;
    }

    public function getEmail():String{
        return $this->email;
    }

    public function getClass():String{
        return $this->class;
    }

    public function getBalance():Float{
        return $this->balance;
    }

    public function getPass(){
        return $this->password;
    }

    //Setters
    public function setId(int $id){
        $this->id=$id;
    }

    public function setName(String $name){
        $this->name=$name;
    }

    public function setSurname(String $surname){
        $this->surname=$surname;
    }

    public function setEmail(String $email){
        $this->email=$email;
    }

    public function setClass(String $class){
        $allowedClass = Korisnik::getAllowedClass();
        if (!in_array($class, $allowedClass)) {
            throw new InvalidArgumentException("$class nije validna klasa");
        }
        else $this->class=$class;
    }

    public function addBalance(float $balance){
        $this->balance+=$balance;
    }

    public function setPass($pass){
         $this->password=$pass;
    }

}
