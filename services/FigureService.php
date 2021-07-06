<?php
set_include_path('C:\Users\User\PhpstormProjects\dont_be_mad_dude_MVC');
require 'models/Figure.php';

class FigureService
{
    public PlayerService $playerService;
    public Helper $helper;
    
    public function __construct (PlayerService $playerService, Helper $helper)
    {
        $this->playerService = $playerService;
        $this->helper = $helper;
    }
    
    public function createFigure(Player $player, array $starterCoords) : Figure
    {
        $name = $this->findFreeName($player);
        $y = $starterCoords[0];
        $x = $starterCoords[1];
        $compass = $this->setCompass($player->team);

        return new Figure($name, $y, $x, $compass);
    }

    public function removeFigure(Board $board, string $figName)
    {
        $playerKey = $this->playerService->findPlayerByFig($board, $figName); // loop through all players and their figures until u find the same figure name as hitFigure
        $figureKey = $this->findFigureKey($board, $figName);
        array_splice($board->players[$playerKey]->figures, $figureKey, 1);
    }

    public function findFreeName(object $player) : string
    {
        $ownedFigures = [];
        foreach ($player->figures as $figure) {
            array_push($ownedFigures, $figure->name);
        }

        for ($i = 1; $i <= 4; $i++) {
            $name = $player->team . $i;
            if (!in_array($name, $ownedFigures)) {

                return $name;
            }
        }
    }

    public function findFigureKey(object $board, string $name) : int
    {
        // loop through all players and their figures until u find the same figure name as the passed $name
        foreach ($board->players as $player) {
            foreach ($player->figures as $key=>$figure){
                if ($figure->name === $name) {

                    return $key;
                }
            }
        }
    }

    public function getFigureNames (array $figuresList) : array
    {
        $namesList = [];
        foreach ($figuresList as $figure) {
            array_push($namesList, $figure->name);
        }

        return $namesList;
    }

    public function findEnemies (array $namesList, string $exclusion) : bool|array
    {    // ['R1', 'G1', 'Y1', 'G2', 'B1], 'R'
        $nonFriendly = $this->preventFriendlyFire($namesList, $exclusion); // ['G1', 'Y1', 'G2', 'B1]
        if (count($nonFriendly) === 0) { //if after removing friendly units
            return false; // no collision because all units there were your own - no point to check further
            //an empty array can also be returned and it would evaluate to false in if($nonFriendly) statement
        }

        //otherwise check the list of remaining names for fortified units - 2+figures of the same team render all figs invincible
        return $this->findVictims($nonFriendly); // ['Y1', 'B1']
    }

    public function preventFriendlyFire (array $namesList, string $exclusion) : array
    {
        foreach ($namesList as $key=>$figure) {
            if ($figure[0] == $exclusion) {
                unset($namesList[$key]); // retaining the order of keys in local variable is not important here
            }
        }

        return $namesList;
    }

    public function findVictims ($targets)
    {
        $enemiesCounter = [
            "R" => 0,
            "G" => 0,
            "B" => 0,
            "Y" => 0,
        ];

        foreach ($targets as $figure) { // keep track of the occurence of figures from each team
            if (in_array($figure[0], array_keys($enemiesCounter))) { // if the figure's team (G) finds a match in the enemies counter
                $enemiesCounter[$figure[0]]++; // increment 1
            }
        }

        $singles = $this->findSingles($enemiesCounter); // ['Y', 'B']

        if($singles){
            $victims = $this->findTargets($targets, $singles); // will remove from ['G1', 'Y1', 'G2', 'B1] all that is not of Y or B team

            return $victims; // ['Y1', 'B1']
        }

        return false;
    }

    public function findSingles (array $enemiesCounter) : array
    {
        $victimsList = [];
        foreach ($enemiesCounter as $team => $count) { // we can attack only single figures
            if ($count === 1) {
                array_push($victimsList, $team); // so we add the team tags to list of potential victims
            }
        }
        return $victimsList;
    }

    public function findTargets (array $initialList, array $victimsList) : array
    {
        $targets = $initialList;
        foreach ($targets as $key => $figure) { // finally we know which teams have exposed figures
            if (in_array($figure[0], $victimsList) === false) { // from the original list of figures
                unset($initialList[$key]); // remove the figures absent in the victims list (0 or 2+ figs)
            }
        }
        return $targets;
    }

    public function updateCompass(Board $board, Figure $figure)
    {
        $breakPoints = [
            "W" =>  ["y" => $this->helper->getMiddle($board->field),
                "x" => 0],

            "N" =>  ["y" => 0,
                "x" => $this->helper->getMiddle($board->field)],

            "E" =>  ["y" => $this->helper->getMiddle($board->field),
                "x" => $this->helper->getLast($board->field)],

            "S" =>  ["y" => $this->helper->getLast($board->field),
                "x" => $this->helper->getMiddle($board->field)],
        ];

        if ($figure->y == $breakPoints["W"]["y"] && $figure->x == $breakPoints["W"]["x"]) {
            if ($board->players[$board->currentPlayer]->team === "R") { #red players at this breakpoint can move to the finish line
                $figure->compass = "F";
            } else {
                $figure->compass = "W";
            }
        } elseif ($figure->y == $breakPoints["N"]["y"] && $figure->x == $breakPoints["N"]["x"]) {
            if ($board->players[$board->currentPlayer]->team === "G") { #green players at this breakpoint can move to the finish line
                $figure->compass = "F";
            } else {
                $figure->compass = "N";
            }
        } elseif ($figure->y == $breakPoints["E"]["y"] && $figure->x == $breakPoints["E"]["x"]) {
            if ($board->players[$board->currentPlayer]->team === "B") {
                $figure->compass = "F";
            } else {
                $figure->compass = "E";
            }
        } elseif ($figure->y == $breakPoints["S"]["y"] && $figure->x == $breakPoints["S"]["x"]) {
            if ($board->players[$board->currentPlayer]->team === "Y") {
                $figure->compass = "F";
            } else {
                $figure->compass = "S";
            }
        }
    }

    public function setCompass(string $team) : string {
        switch ($team) {
            case "R":

                return "W";

            case "G":

                return "N";

            case "B":

                return "E";

            case "Y":

                return "S";
        }
    }
}