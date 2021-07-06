<?php


class Player
{
    public string $team;
    public array $figures;
    public Figure|null $currentFigure;
    public int $finishedCount;

    function __construct(string $team) {
        $this->team = $team;
        $this->figures = [];
        $this->currentFigure = null;
        $this->finishedCount = 0;

    }
}