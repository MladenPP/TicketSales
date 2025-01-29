<?php

namespace ProdajaKarata\controller;

use ProdajaKarata\core\Db;
use ProdajaKarata\core\Request;
use ProdajaKarata\core\Config;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

abstract class AbstractController {
    protected $request;
    protected $db;
    protected $config;
    protected $view;
    protected $log;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->db = Db::getinstance();
        $this->config = Config::getInstance();

        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
        $this->view = new \Twig\Environment($loader);

        $this->log = new Logger('ProdajaKarata');
        $logFile = $this->config->get('log');

        $this->log->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
    }

    public function setCustomerId(int $customerId){
        $this->customerId = $customerId;
    }

    protected function render(string $template, array $params): string {
        return $this->view->load($template)->render($params);
    }
}