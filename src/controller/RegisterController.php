<?php 

namespace ProdajaKarata\controller;

use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\model\KorisnikModel;
use ProdajaKarata\model\KartaModel;
use ProdajaKarata\model\AbstractKartaModel;
use ProdajaKarata\controller\UserController;
use DateTime;

class RegisterController extends AbstractController{

public function check(){
    if(!isset($_SESSION['user'])){
        $properties=['errorMessage'=>'Nije dozvoljen pristup'];
        $return=[$this->render('guestMeni.twig', []),$this->render('error.twig', $properties)];
        return $return;
    }
    $model=new KorisnikModel($this->db);
    $korisnik=$model->get($_SESSION['user']);
    $class=$korisnik->getClass();
    if($class!="Admin" && $class!="Menadzer"){
        $properties=['errorMessage'=>'Nije dozvoljen pristup'];
        $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
        return $return;
    }
    $ret=['valid',$class];
    return $ret;   
}

public function register(String $s='', $id=0){
        $properties=['errorMessage'=>$s];
        if($id!=0){
            $model=new KorisnikModel($this->db);
            $korisnik=$model->get($_SESSION['user']);
            $class=$korisnik->getClass();
            try {
                $view=$model->get($id);
            } catch (\Throwable $th) {
                $properties=['errorMessage'=>'Korisnik ne postoji'];
                $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
            }
            
            if($view->getClass()=='Admin' && $class !='Admin'){
                $properties=['errorMessage'=>'Nije dozvoljen pristup'];
                $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
                return $return;
            }
            $properties['class']=$class;
            if($_SESSION['user'] == $id){
                $properties['class']='Korisnik';
            }
            $properties['id']="/$id";
            $return=[$this->render($class.'Meni.twig', []),$this->render('register.twig', $properties)];
            return $return;
        }
        $return[] = $this->render('register.twig', $properties);
        return $return;
    }

    public function reg($id=0){
        if(!isset($_SESSION['user'])){
            return $this->add();
        }
        $p=$this->request->getParams();
        return $this->edit($id,$p);
    }

    public function add(){
        $p=$this->request->getParams();
        $model=new KorisnikModel($this->db);
        if($p->get('email')=='' || $p->get('ime')=='' || $p->get('prezime')=='' || $p->get('pass')==''){
            return $this->register("Popunite sva polja");
        }
        try {
            $email=$p->get('email');
            $model->getByEmail($email);
        } catch (NotFoundException $th) {
            $model->create($p->get('ime'),$p->get('prezime'),$p->get('email'),'Korisnik',0,$p->get('pass'));
            return $this->register("Uspesno ste se registrovali");
        }
        return $this->register("Korisnik sa emailom: $email vec postoji");

    }

    public function edit($id,$p){
        
        if($p->get('email')=='' && $p->get('ime')=='' && $p->get('prezime')=='' && $p->get('klasa')=='' && $p->get('balans')=='' && $p->get('pass')=='' ){
            return $this->register("Popunite bar jedno polje",$id);
        }
        $model= new KorisnikModel($this->db);
        try {
            $korisnik=$model->get($id);
        } catch (\Throwable $th) {
            $properties=['errorMessage'=>'Korisnik ne postoji'];
            $return=[$this->render('AdminMeni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        if($p->get('email')!=''){
            $model->edit('email', $p->get('email'), $id);
        }
        if($p->get('ime')!=''){
            $model->edit('name', $p->get('ime'), $id);
        }
        if($p->get('prezime')!=''){
            $model->edit('surname', $p->get('prezime'), $id);
        }
        if($p->get('balans')!=''){
            $model->edit('balance', (float)$p->get('balans'), $id);
        }
        if($p->get('klasa')!=''){
            try {
                $model->edit('class', $p->get('klasa'), $id);
            } catch (\Throwable $th) {
                return $this->register("Klasa nije validna",$id);
            }
        }
        if($p->get('pass')!=''){
            $model->edit('password', $p->get('pass'), $id);
        }
        $check=$this->check();
        $userController= new UserController($this->request);
            if($check[0] !='valid'){
                return $userController->profil();
            }
        return $userController->korisnici();
        
    }

    public function ticket($id){
        $check=$this->check();
        $class=$check[1];
        if($check[0] !='valid'){
            return $check;
        }
        if($class!="Admin"){
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        if(!$this->request->isPost()) {
            return $a=[$this->render('AdminMeni.twig', []), $this->render('kartareg.twig', [])];
        }

        $p=$this->request->getParams();
        if($p->get('utakmica')=='' || $p->get('vreme')=='' || $p->get('kolicina')=='' || $p->get('cena')=='' || $p->get('orijentacija')==''){
            $properties=['errorMessage'=>'Popunite sva polja'];
            return $a=[$this->render('AdminMeni.twig', []), $this->render('kartareg.twig', $properties)];
        }
        $time = $this->formatTime($p->get('vreme'));
        $model=new KartaModel($this->db);
        $absmodel=new AbstractKartaModel($this->db);
        try {
            $o=$absmodel->getByOrientation($p->get('orijentacija'));
            try {
                $pr=$absmodel->getByPrice((float)$p->get('cena'),'=');
            } catch (\Throwable $th) {
                $absmodel->create($p->get('orijentacija'),(float)$p->get('cena'));
                $karte=$absmodel->getAll();
                $abskarta=end($karte);
                $model->create($id,$abskarta->getId(),$time,$p->get('utakmica'),(int)$p->get('kolicina'));
                $userController= new UserController($this->request);
                return $userController->adminStad(1,'valid');
            }
        } catch (\Throwable $th) {
            $absmodel->create($p->get('orijentacija'),(float)$p->get('cena'));
            $karte=$absmodel->getAll();
            $abskarta=end($karte);
            $model->create($id,$abskarta->getId(),$time,$p->get('utakmica'),(int)$p->get('kolicina'));
            $userController= new UserController($this->request);
            return $userController->adminStad(1,'valid');
        }
        foreach($o as $or){
            foreach ($pr as $pri){
                if ($or->getId()==$pri->getId()){
                    $model->create($id,$or->getId(),$time,$p->get('utakmica'),(int)$p->get('kolicina'));
                    $userController= new UserController($this->request);
                    return $userController->adminStad(1,'valid');
                }
            }
        }
        $absmodel->create($p->get('orijentacija'),(float)$p->get('cena'));
        $karte=$absmodel->getAll();
        $abskarta=end($karte);
        $model->create($id,$abskarta->getId(),$time,$p->get('utakmica'),(int)$p->get('kolicina'));
        $userController= new UserController($this->request);
        return $userController->adminStad();
    }

    public function formatTime($time){
        $dateTime = new DateTime($time);
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        return $formattedDateTime;
    }

    public function deleteK($id){
        $check=$this->check();
        $class=$check[1];
        if($check[0] !='valid'){
            return $check;
        }
        if($class!="Admin"){
            $properties=['errorMessage'=>'Nije dozvoljen pristup'];
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
            return $return;
        }
        $cmodel=new KorisnikModel($this->db);
        try {
            $cmodel->delete($id);
        } catch (\Throwable $th) {
        $properties=['errorMessage'=>'Korisnik ne postoji'];
        $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig', $properties)];
        return $return;
        }
        
        $userController= new UserController($this->request);
        return $userController->korisnici();
    }

}
