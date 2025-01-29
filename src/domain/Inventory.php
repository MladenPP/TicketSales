<?php

namespace ProdajaKarata\domain;

class Inventory {

    private int $id;
    private $customerId;
    private $ticketId;
    private $amount;

     //Getters
     public function getId():int {
        return $this->id;
    }

    public function getCustomerId():int{
        return $this->customerId;
    }

    public function getTicketId():int{
        return $this->ticketId;
    }

    public function getAmount(): int{
        return $this->amount;
    }

    //Setters
    public function setId(int $id){
        $this->id=$id;
    }

    public function setCustomerId(int $customerId){
        $this->customerId=$customerId;
    }

    public function setTicketId(int $ticketId){
        $this->ticketId=$ticketId;
    }

    public function setAmount(int $amount){
        $this->amount=$amount;
    }

    public function editAmount(String $plusminus,int $amount){
        if($plusminus == '+'){$this->amount+=$amount;}
        elseif($plusminus == '-'){$this->amount-=$amount;}
        else{throw new InvalidArgumentException("$plusminus nije odgovarajuci znak (+,-)");}
    }

}