<?php
set_include_path('C:\Users\User\PhpstormProjects\dont_be_mad_dude_MVC');
require "services/pregameService.php";


class pregameController {
    public View $view;
    public PregameService $pregameService;

    public function __construct(View $view, PregameService $pregameService)
    {

        $this->view = $view;
        $this->pregameService = $pregameService;

    }


    public function createPlayers(Board $board) : Board
    { // makes Player obj and appends to the board
        $teamsList = ["R", "G", "B", "Y"];
        $playersNum = $this->view->getPlayersNum();

        for ($i = 0; $i < $playersNum; $i++) {
            array_push($board->players, $this->pregameService->makePlayer($teamsList[$i]));
        }

        return $board;
    }


    public function setStarterPlayer(Board $board) : Board
    {
        $board->currentPlayer = 0;

        return $board;
    }


    public function getBoard() : Board
    {
        // takes a Board obj that has only $field set
        $board = $this->createPlayers($this->pregameService->makeBoard()); // appends players to $board->players

        return $this->setStarterPlayer($board); // returns the board with player set to 0
    }
}

