<?php


class Figure
{
    public string $name;
    public int $y;
    public int $x;
    public string $compass;

    function __construct(string $name, int $y, int $x, string $compass){
        $this->name = $name;
        $this->y = $y;
        $this->x = $x;
        $this->compass = $compass;
    }
}