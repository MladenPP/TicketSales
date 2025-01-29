<?php 

namespace ProdajaKarata\controller;

use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\model\KorisnikModel;
use ProdajaKarata\model\SupportModel;

class SupportController extends AbstractController{

    public function message(){
        if(!isset($_SESSION['user'])){
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render('guestMeni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        $model=new KorisnikModel($this->db);
        $korisnik=$model->get($_SESSION['user']);
        if($korisnik->getClass()!="Korisnik"){
            $class=$korisnik->getClass();
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        if($this->request->isPost()){
            $p=$this->request->getParams();
            if($p->get('poruka')==''){
                $properties=['message'=>'Unesite poruku','korisnik'=>1];
                return $a=[$this->render('KorisnikMeni.twig', []),$this->render('message.twig', $properties)];
            }
            $smodel= new SupportModel($this->db);
            $id=$_SESSION['user'];
            $msg=$p->get('poruka');
            $smodel->create($id,$msg);
            $properties=['message'=>'Poruka poslata','korisnik'=>1];
            $this->log->info("Korisnik id= $id je prijavio problem: $msg");
            return $a=[$this->render('KorisnikMeni.twig', []),$this->render('message.twig', $properties)];
        }
        $properties=['korisnik'=>1];
        return $a=[$this->render('KorisnikMeni.twig', []),$this->render('message.twig', $properties)];

    }

    public function list($page){
        if(!isset($_SESSION['user'])){
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render('guestMeni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        $model=new KorisnikModel($this->db);
        $korisnik=$model->get($_SESSION['user']);
        if($korisnik->getClass()!="Menadzer"){
            $class=$korisnik->getClass();
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        $smodel= new SupportModel($this->db);
        $messages=$smodel->getAllS($page,3);
        $all=$smodel->getAll();
        $lastpage=ceil(count($all)/3);
        $properties=['messages'=>$messages,'currentPage'=>$page,'lastPage'=>$lastpage];
        $return=[$this->render('MenadzerMeni.twig', []),$this->render('messages.twig', $properties)];
        return $return;

    }

    public function reply($id){
        if(!isset($_SESSION['user'])){
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render('guestMeni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        $model=new KorisnikModel($this->db);
        $korisnik=$model->get($_SESSION['user']);
        if($korisnik->getClass()!="Menadzer"){
            $class=$korisnik->getClass();
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        if($this->request->isPost()){
            $p=$this->request->getParams();
            if($p->get('poruka')==''){
                $properties=['message'=>'Unesite poruku'];
                return $a=[$this->render('KorisnikMeni.twig', []),$this->render('message.twig', $properties)];
            }
            $smodel= new SupportModel($this->db);
            $idd=$_SESSION['user'];
            $msg=$p->get('poruka');
            $smodel->reply($id,$msg);
            $properties=['message'=>'Odgovor poslat'];
            $this->log->info("Menadzer id= $idd odgovorio je na poruku id= $id sa: $msg");
            return $a=[$this->render('KorisnikMeni.twig', []),$this->render('message.twig', $properties)];
        }
        return $a=[$this->render('MenadzerMeni.twig', []),$this->render('message.twig', [])];

    }

    public function replies($page){
        if(!isset($_SESSION['user'])){
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render('guestMeni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        $model=new KorisnikModel($this->db);
        $korisnik=$model->get($_SESSION['user']);
        if($korisnik->getClass()!="Korisnik"){
            $class=$korisnik->getClass();
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        $smodel= new SupportModel($this->db);
        try {
            $messages=$smodel->getAllSwithId($page,3,$_SESSION['user']);
            $all=$smodel->getAllwithId($_SESSION['user']);
        } catch (\Throwable $th) {
            $properties=['msg'=>'Nemate Odgovore','korisnik'=> 1];
            $return=[$this->render('KorisnikMeni.twig', []),$this->render('message.twig', $properties)];
            return $return;
        }
        $lastpage=ceil(count($all)/3);
        $properties=['messages'=>$messages,'currentPage'=>$page,'lastPage'=>$lastpage,'korisnik'=>1];
        $return=[$this->render('KorisnikMeni.twig', []),$this->render('messages.twig', $properties)];
        return $return;

    }
    



}