<?php

namespace ProdajaKarata\domain;

class Karta {

    private int $id;
    private $stadiumId;
    private $ticketId;
    private $time;
    private $game;
    private $amount;

     //Getters
     public function getId():int {
        return $this->id;
    }

    public function getStadiumId():int{
        return $this->stadiumId;
    }

    public function getTicketId():int{
        return $this->ticketId;
    }

    public function getTime():String{
        return $this->time;
    }

    public function getGame():String{
        return $this->game;
    }

    public function getAmount():int{
        return $this->amount;
    }

    //Setters
    public function setId(int $id){
        $this->id=$id;
    }

    public function setStadiumId(String $stadiumId){
        $this->stadiumId=$stadiumId;
    }

    public function setTicketId(String $ticketId){
        $this->ticketId=$ticketId;
    }

    public function setTime(String $time){
        $this->time=$time;
    }

    public function setGame(String $game){
        $this->info=$gameS;
    }

    public function setAmount(int $amount){
        $this->amount=$amount;
    }
    
}