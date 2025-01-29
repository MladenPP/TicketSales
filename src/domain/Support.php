<?php

namespace ProdajaKarata\domain;

class Support {
    private $id;
    private $senderid;
    private $message;
    private $reply;
    private bool $answered; 

    //Getters
    public function getId():int {
        return $this->id;
    }

    public function getSenderId():int{
        return $this->senderid;
    }

    public function getMessage():String{
        return $this->message;
    }

    public function getReply():String{
        return $this->reply;
    }

    public function isAnswered():bool{
        return $this->answered;
    }

    //Setters
    public function setId($id){
        $this->id=$id;
    }

    public function setSenderId($id){
        $this->senderid=$id;
    }

    public function setMessage($message){
        $this->message=$message;
    }

    public function setreply($reply){
        $this->reply=$reply;
    }

    public function setAnswer($a){
        $this->answered=$a;
    }


}