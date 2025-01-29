<?php 

namespace ProdajaKarata\controller;

class ErrorController extends AbstractController {
    public function notFound(): array{
        $properties = ['errorMessage' => 'Page not found'];
        $a=[$this->render('error.twig', $properties)];
        return $a;
    }
}