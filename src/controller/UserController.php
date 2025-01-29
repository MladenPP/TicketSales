<?php 

namespace ProdajaKarata\controller;

use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\model\KorisnikModel;
use ProdajaKarata\model\StadionModel;
use ProdajaKarata\model\KartaModel;
use ProdajaKarata\model\InventoryModel;
use ProdajaKarata\controller\GuestController;
use TCPDF;

class UserController extends AbstractController{

    public function stadion($id):array{

        $model=new StadionModel($this->db);
        $stadion=$model->get($id);
        $properties=['stadion'=>$stadion];
        $guestController = new GuestController($this->request);
        $return = $guestController->login();
        $return[1] = $this->render('stadion.twig', $properties);
        return $return;
        
    }

    public function logout(){
        $id=$_SESSION['user'];
        $this->log->info("Odjavljivanje korisnika id= $id ");
        unset($_SESSION['user']);
        session_destroy();
        header("Location: /");
    }

    public function search(){
        $p=$this->request->getParams();

        $model=new StadionModel($this->db);
        try {
            $stadioni=$model->getByName($p->get('imeStadiona'));
        } catch (\Throwable $th) {
            $guestController = new GuestController($this->request);
            $return = $guestController->login();
            $properties=['currentPage'=>1,'lastPage'=>1];
            $return[1] = $this->render('stadioni.twig',$properties);
            return $return;
        }
        
        $properties=['stadioni'=>$stadioni,'currentPage'=>1,'lastPage'=>1];
        $guestController = new GuestController($this->request);
        $return = $guestController->login();
        $return[1] = $this->render('stadioni.twig', $properties);
        return $return;
    }

    public function ticket($id,$page){
        $model=new KartaModel($this->db);
        $karte=$model->forprintAll($id,$page,3);
        $guestController = new GuestController($this->request);
        $return = $guestController->login();
        $properties=['karte'=>$karte,'currentPage'=>$page,'currentStadium'=>$id];
        $return[1] = $this->render('karte.twig', $properties);
        return $return;
    }

    public function buy($id){
        $model=new KartaModel($this->db);
        $karta=$model->forprint($id);
        $guestController = new GuestController($this->request);
        $return = $guestController->login();
        if(!isset( $_SESSION['user'])){
            $properties=['errorMessage'=>'Prijavite se da bi kupovali','buy'=>true];
            $return=[$this->render('error.twig',$properties)];
            return $return;
        }
        $cusmodel=new KorisnikModel($this->db);
        $customer=$cusmodel->get($_SESSION['user']);
        $params = $this->request->getParams();
        $price=$params->get('amount')*$karta['ticprice'];
        if($customer->getBalance()<$price){
            $properties=['errorMessage'=>'Nemate dovoljno sredstava','buy'=>true];
            $class=$customer->getClass();
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig',$properties)];
            return $return;
        }

        try {
            $model->buy($params->get('amount'), $id, $_SESSION['user']);
        } catch (\Throwable $th) {
            $properties=['errorMessage'=>'Problem sa kupovinom','buy'=>true];
            $class=$customer->getClass();
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig',$properties)];
            return $return;
        }
        $properties=['errorMessage'=>'Uspesna Kupovina','buy'=>true];
        $class=$customer->getClass();
        $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig',$properties)];
        $idd=$_SESSION['user'];
        $a=$params->get('amount');
        $this->log->info("Korisnik id= $idd kupio je $a karte sa id=$id");
        return $return;
    }

    public function profil($call='korisnik'){
        if(!isset( $_SESSION['user'])){
            $properties=['errorMessage'=>'Niste prijavljeni','buy'=>true];
            $return=[$this->render('error.twig',$properties)];
            return $return;
        }
        $model=new KorisnikModel($this->db);
        $korisnik=$model->get($_SESSION['user']);
        $class=$korisnik->getClass();
        $invmodel= new InventoryModel($this->db);
        try {
            $inv=$invmodel->forprint($_SESSION['user']);
        } catch (\Throwable $th) {
            $properties=['korisnik'=>$korisnik];
        $return=[$this->render($class.'Meni.twig', []),$this->render($call.'.twig',$properties)];
        return $return;
        }
        
        $properties=['korisnik'=>$korisnik,'inventory'=>$inv];
        $return=[$this->render($class.'Meni.twig', []),$this->render($call.'.twig',$properties)];
        return $return;

    }

    public function uplata(){
        $params = $this->request->getParams();
        if($params->get('uplata')!==null && $params->get('uplata')!=''){
        $params->get('uplata');
        $model=new KorisnikModel($this->db);
        $model->reduceadd('+',$params->get('uplata'),$_SESSION['user']);
        $id=$_SESSION['user'];
        $u=$params->get('uplata');
        $this->log->info("Korisnik id= $id Uplatio je $u RSD ");
        return $this->profil('uplata');
        }
        return $this->profil('uplata');
    }

    public function adminStad($page=1, $s=''){
        if(!isset( $_SESSION['user'])){
            $properties=['errorMessage'=>'Nije dozvoljen pristup','buy'=>true];
            $return=[$this->render('guestMeni.twig', []),$this->render('error.twig',$properties)];
            return $return;
        }
        $model=new KorisnikModel($this->db);
        $korisnik=$model->get($_SESSION['user']);
        $class=$korisnik->getClass();
        if($class!='Admin'){
            $properties=['errorMessage'=>'Nije dozvoljen pristup','buy'=>true];
            $return=[$this->render($class.'Meni.twig', []),$this->render('error.twig',$properties)];
            return $return;
        }
        $model=new StadionModel($this->db);
        $stadioni=$model->getAll($page,4);
        $stadionic=$model->getAll(1,9999);
        $lastpage=ceil(count($stadionic)/4);
        $properties=['stadioni'=>$stadioni,'currentPage'=>$page,'lastPage'=>$lastpage,'admin'=>'admin'];
        if($this->request->isPost()){
        $params = $this->request->getParams();
        if($params->get('stadime')!='' && $params->get('stadinfo')!=''){
            try {
                $model->create($params->get('stadime'),$params->get('stadinfo'));
                return $a=[$this->render('AdminMeni.twig', []), $this->render('stadioni.twig', $properties)];
            } catch (\Throwable $th) {
                $properties['errorMessage']='Stadion vec postoji';
                return $a=[$this->render('AdminMeni.twig', []), $this->render('stadioni.twig', $properties)];
            }
        }
        if($s==''){$properties['errorMessage']='polja ne smeju biti prazna';}
        }
        return $a=[$this->render('AdminMeni.twig', []), $this->render('stadioni.twig', $properties)];
    }

    public function korisnici($page=1){
        if(!isset( $_SESSION['user'])){
            $properties=['errorMessage'=>'Nije dozvoljen pristup','buy'=>true];
            $return=[$this->render('guestMeni.twig', []),$this->render('error.twig',$properties)];
            return $return;
        }
        $model= new KorisnikModel($this->db);
        try {
            $user=$model->get($_SESSION['user']);
        } catch (\Throwable $th) {
            $this->logout();
            
        }
        
        $class=$user->getClass();
        if($class!="Admin" && $class!="Menadzer"){
            $properties=['errorMessage'=>'Nije dozvoljen pristup','buy'=>true];
            $return=[$this->render($class.'Meni.twig',[]),$this->render('error.twig',$properties)];
            return $return;
        }
        $korisnici = $model->getAllS($page,3);
        $properties=['korisnici'=>$korisnici,'currentPage'=>$page,'class'=>$class];
        return $a=[$this->render($class.'Meni.twig', []), $this->render('korisnici.twig', $properties)];

    }

    public function pdf(){
        $pdf = new TCPDF(); 
        $pdf->SetCreator('ProdajaKarata');
        $pdf->SetAuthor('ProdajaKarata');
        $pdf->SetTitle('Korisnici PDF');
        $pdf->SetSubject('Korisnici');
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $model=new KorisnikModel($this->db);
        $korisnici=$model->getAll();
        $print='';
        foreach($korisnici as $korisnik){
            $print=$print.'name: '.$korisnik->getName()."\n";
            $print=$print.'surname: '.$korisnik->getSurname()."\n";
            $print=$print.'email: '.$korisnik->getEmail()."\n";
            $print=$print.'class: '.$korisnik->getClass()."\n";
            $print=$print.'balance: '.$korisnik->getBalance().'RSD'."\n";
            $print=$print."\n";
        }
        $pdf->MultiCell(0, 10, $print);
        $pdf->Output('korisnici.pdf', 'I');
        

    }

}