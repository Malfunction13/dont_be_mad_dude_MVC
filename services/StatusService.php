<?php


class StatusService
{
    public Helper $helper;

    public function __construct (Helper $helper)
    {
        $this->helper = $helper;
    }

    public function allowedToSpawn (object $player) : bool
    {
        if (count($player->figures) + $player->finishedCount < 4) {

            return true;
        } else {

            return false;
        }
    }

    public function isFinishedFigure(Board $board) : bool
    {
        $middle = $this->helper->getMiddle($board->field);
        if ($board->players[$board->currentPlayer]->currentFigure->y == $middle &&
            $board->players[$board->currentPlayer]->currentFigure->x == $middle) {

            return true;
        }

        return false;
    }


    public function isFinishedPlayer(Player $player) : bool
    {
        if ($player->finishedCount === 4) { // if the player

            return true;
        }
        return false;
    }
}