<?php
set_include_path('C:\Users\User\PhpstormProjects\dont_be_mad_dude_MVC');
require 'controllers/GameController.php';
require 'controllers/PregameController.php';
require 'views/View.php';

require 'helpers/Helper.php';
require_once 'services/StatusService.php';
require_once 'services/PlayerService.php';
require_once 'services/FigureService.php';
require_once 'services/MovementService.php';


$helper = new Helper();
$sService = new StatusService($helper);
$pService = new PlayerService();
$fService = new FigureService($pService,$helper);
$mService = new MovementService($helper);
$pgService = new PregameService($helper);
$view = new View($helper);
$pregameController = new pregameController($view, $pgService);
$board = $pregameController->getBoard();
$controller = new gameController($board, $view, $pService, $fService, $mService, $sService, $helper);

function main(object $controller) {
    while (true) {
        if ($controller->isGameOver()) { // exit condition first
             $controller->finalResult();

             break;
        }

        if ($controller->handleTurn()) { // will return false if the player got bonus turn and it is still his turn to play
            $controller->switchPlayer(); // if currPlayer is unchanged on next turn the throw will be for the same player
        }

        $controller->view->printBoard($controller->board->field);
        sleep(2);
    }

}

main($controller);


