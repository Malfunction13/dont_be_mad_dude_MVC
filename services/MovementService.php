<?php


class MovementService
{
    public Helper $helper;
    
    public function __construct(Helper $helper) {
        $this->helper = $helper;
    }
    
    public function move(Board $board, Figure $figure)
    {
        switch ($figure->compass) {
            case "W":
                $this->moveWest($board, $figure);
                break;

            case "N":
                $this->moveNorth($board, $figure);
                break;

            case "E":
                $this->moveEast($board, $figure);
                break;

            case "S":
                $this->moveSouth($board, $figure);
                break;

            case "F":
                $this->moveFinish($board, $figure);
                break;

        }
    }

    // Movement public functions only modify the objects Y, X
    // West never looks left and down
    public function moveWest (Board $board, Figure $figure)
    {
        if (array_key_exists($figure->y-1, $board->field) && $board->field[$figure->y-1][$figure->x][0] != "  ") {
            $figure->y --; // change the X coordinate in the figure obj accordingly
        } elseif ($board->field[$figure->y][$figure->x+1][0] != "  ") {
            $figure->x ++;
        }
    }

    // North never looks left and up
    public function moveNorth (Board $board, Figure $figure)
    {
        if (array_key_exists($figure->x+1, $board->field[$figure->y]) &&
            $board->field[$figure->y][$figure->x+1][0] != "  ") {
            $figure->x ++; // change the X coordinate in the figure obj accordingly
        } elseif ($board->field[$figure->y+1][$figure->x][0] != "  ") {
            $figure->y ++;
        }

    }

    public function moveEast (Board $board, Figure $figure)
    {
        if (array_key_exists($figure->y+1, $board->field)  && $board->field[$figure->y+1][$figure->x][0] != "  ") {
            $figure->y ++; // change the X coordinate in the figure obj accordingly
        } elseif ($board->field[$figure->y][$figure->x-1][0] != "  "){
            $figure->x --;
        }
    }

    // South never right and down
    public function moveSouth (Board $board, Figure $figure) {
        if (array_key_exists($figure->x-1, $board->field[$figure->y]) && $board->field[$figure->y][$figure->x-1][0] != "  ") {
            $figure->x --; // change the X coordinate in the figure obj accordingly
        } elseif ($board->field[$figure->y-1][$figure->x][0] != "  "){
            $figure->y --;
        }
    }

    // On the finish line will look just in 1 direction
    public function moveFinish (Board $board, Figure $figure)
    {
        if ($figure->compass === "F" && $board->players[$board->currentPlayer]->team === "R") {
            $figure->x ++;
        } elseif ($figure->compass === "F" && $board->players[$board->currentPlayer]->team === "G") {
            $figure->y ++;
        } elseif ($figure->compass === "F" && $board->players[$board->currentPlayer]->team === "B") {
            $figure->x --;
        } elseif ($figure->compass === "F" && $board->players[$board->currentPlayer]->team === "Y") {
            $figure->y --;
        }
    }

    public function newPosUpdate(array &$field, Figure $figure, bool|string $collision) {
        if ($collision) {
            if ($collision == "F") { //finishing condition
                //do nothing because at the finishing spot the figure gets deleted
            } else { // if there is standard collision
                $key = array_search($collision, $field[$figure->y][$figure->x]); // seek
                array_splice($field[$figure->y][$figure->x], $key, 1); // and destroy
                array_push($field[$figure->y][$figure->x], $figure->name); //occupy slot
            }
        } else { // if there is no collision we have a free tile or occupied by our own figures tile
            if ($field[$figure->y][$figure->x][0] === ".." || $field[$figure->y][$figure->x][0] === "FF") {
                array_splice($field[$figure->y][$figure->x], 0, 1); //remove the free tile
                array_push($field[$figure->y][$figure->x], $figure->name); // step on it
            } else {
                array_push($field[$figure->y][$figure->x], $figure->name);
            }

        }
    }

    public function oldPosUpdate(Board $board, int $oldY, int $oldX)
    {
        $key = array_search($board->players[$board->currentPlayer]->currentFigure->name, $board->field[$oldY][$oldX]); //find the key for the currentPlayers figure on old position
        array_splice($board->field[$oldY][$oldX],$key,1); //remove using that key

        $last = $this->helper->getLast($board->field);
        $middle = $this->helper->getMiddle($board->field);

        if (count($board->field[$oldY][$oldX]) === 0) { //if the array remains empty
            //if the old coordinates are on the finish lines
            if ($oldY == $middle && ($oldX > 0 && $oldX < $last)) {
                //horizontal line
                array_push($board->field[$oldY][$oldX], "FF"); // fill the empty slot with a FF marker
            } elseif ($oldX == $middle && ($oldY > 0 && $oldY < $last)) {
                //this is the vertical finish line
                array_push($board->field[$oldY][$oldX], "FF");
            } else { // if the tile is not within the finish line than its a casual free tile
                array_push($board->field[$oldY][$oldX], ".."); // add a free tile marker
            }
        }
    }
}