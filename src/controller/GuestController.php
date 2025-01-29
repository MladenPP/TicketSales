<?php 

namespace ProdajaKarata\controller;

use ProdajaKarata\exceptions\NotFoundException;
use ProdajaKarata\model\KorisnikModel;
use ProdajaKarata\model\StadionModel;

class GuestController extends AbstractController {
    
    public function login($page=1): array{
        $model=new StadionModel($this->db);
            $stadioni=$model->getAll($page,4);
            $stadionic=$model->getAll(1,9999);
            $lastpage=ceil(count($stadionic)/4);
            $properties=['stadioni'=>$stadioni,'currentPage'=>$page,'lastPage'=>$lastpage];
            $cmodel=new KorisnikModel($this->db);

        if(isset( $_SESSION['user'])){
            $customer=$cmodel->get( $_SESSION['user']);
            $class=$customer->getClass();
            if($class=='Admin'){
                $properties['admin']='admin';
            }
            return $a=[$this->render($class.'Meni.twig', []), $this->render('stadioni.twig', $properties)];
        }
        
        if(!$this->request->isPost()) {
            
            return $a=[$this->render('guestMeni.twig', []), $this->render('stadioni.twig', $properties)];
        }
        
        $params = $this->request->getParams();
        
        
        if(!$params->has('email') || $params->getString('email')=='' || !$params->has('pass') || $params->getString('pass')==''){
            $params = ['errorMessage' => 'Unesite e-mail i sifru'];
            return $a=[$this->render('guestMeni.twig', $params), $this->render('stadioni.twig', $properties)];
        }

        $email = $params->getString('email');
        $pass = $params->getString('pass');
        try{
            $customer = $cmodel->getByEmail($email);
        } catch (NotFoundException $e){
            $this->log->info('Pokusaj prijavljivanja mejlom koji nije u bazi');
            $params = ['errorMessage' => 'Email nije nadjen.'];
            return $a=[$this->render('guestMeni.twig', $params), $this->render('stadioni.twig', $properties)];
        }
        if($customer->getPass()!=md5($pass)){
            $this->log->info("Pokusaj prijavljivanja mejlom: $email uz pogresnu sifru: $pass");
            $params = ['errorMessage' => 'Sifra nije tacna.'];
            return $a=[$this->render('guestMeni.twig', $params), $this->render('stadioni.twig', $properties)];
        }
        

        $_SESSION['user'] = $customer->getId();

        $class=$customer->getClass();
        if($class=='Admin'){
            $properties['admin']='admin';
        }
        $idd=$customer->getId();
        $this->log->info("Prijavljen korisnik sa id= $idd ");
        return $a=[$this->render($class.'Meni.twig', []), $this->render('stadioni.twig', $properties)];
    }

    public function stadion($id){
        $model=new StadionModel($this->db);
        $stadion=$model->get($id);
        $properties=['stadion'=>$stadion];
        return $a=[$this->render('guestMeni.twig', []), $this->render('stadion.twig', $properties)];

    }

    
}