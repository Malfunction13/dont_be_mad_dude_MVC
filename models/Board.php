<?php


class Board
{
    public array $field;
    public array $players;
    public int $currentPlayer;

    function __construct(array $field) {
        $this->field = $field;
        $this->players = [];
        $this->currentPlayer = 0;
    }
}