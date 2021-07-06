<?php


class View
{
    public Helper $helper;

    public function __construct(Helper $helper) {
        $this->helper = $helper;
    }

    public function printBoard(array $field) {
        echo "\n \n"; // trailing empty lines
        $field = $this->colorize($field);
        foreach ($field as $row) {
            for ($i = 0; $i < count($row); $i++) {
                echo implode("", $row[$i]) . ($i===$this->helper->getLast($field)? "\n" : "  ");
            }

        }
    }


    public function throwDice(object $player) : int {
        $moves = rand(1, 6);
        echo "\n", "$player->team throws: " . $moves;

        return $moves;
    }


    public function needSix() {
        echo "\n", "Don't be mad dude, you need to throw a SIX to put a figure on the field.";
    }


    public function getFigureChoice(array $namesList) : string {
        echo "\n", "Please choose target from the list [", implode(", ", $namesList), "]: ";
        $choice = strtoupper(readline("You have to insert the name as i.e R1 or Y4: "));

        if (!in_array($choice,$namesList)) { //validate input
            echo "\n", "You chose $choice which is not a valid choice! Please choose a target from the list!";

            return $this->getFigureChoice($namesList);
        }

        return $choice;
    }


    public function moveOrSpawn () : string {
        echo "\n", "You can move a figure or spawn a figure (move/spawn): ";
        $choice = strtoupper(readline("Please insert 'move' or 'spawn': "));

        if (!in_array($choice, ["MOVE", "SPAWN"])) { //validate input
            echo "\n", "You chose $choice which is not a valid choice!\nPlease insert move or spawn!";

            return $this->moveOrSpawn();
        }

        return $choice;
    }


    public function getPlayersNum() : int {
        echo "\n", "Please insert number of players. Between 2 and 4 players can participate: ";
        $choice = readline();

        if(is_numeric($choice) && $choice <= 4 && 2 <= $choice) {

            return intval($choice);
        } else {
            echo "\n", "Wrong input! Please insert a number between 2 and 4!";

            return $this->getPlayersNum(); // if the user inserts a gazilion times wrong input it will blow the stack eventually.
        }

    }


    public function turnAnnouncement(object $board) {
        echo "\n", "It is player " . $board->players[$board->currentPlayer]->team . "'s turn.";

        $figCount = count($board->players[$board->currentPlayer]->figures);
        if ($figCount) { //if different than 0

            echo "\n", "You have " . ($figCount==1 ? "1 figure" : "$figCount figures") . " on the field";
        } else {

            echo "\n", "You have 0 figures on the field.";
        }

        $finishedCount = $board->players[$board->currentPlayer]->finishedCount;
        if ($finishedCount) {

            echo "\n", "You have finished with " . ($finishedCount==1 ? "1 figure." : "$finishedCount figures.");
        } else {

            echo "\n", "You have finished with 0 figures";
        }
    }


    public function figureFinished(string $name) {
        echo "\n", "CONGRATULATIONS! You just finished with figure $name";
    }


    public function playerFinished(string $name) {
        echo "\n", "CONGRATULATIONS! Player $name just finished the game with all figures!";
    }


    public function printResult($winnersList) {
        echo "\n", "GAME OVER! THE WINNER IS PLAYER " . $winnersList[0]->team;

        for ($i = 0; $i < count($winnersList); $i++) {
            echo "\n" . "Place " . $i+1 . ": " . $winnersList[$i]->team;
        }
        echo "\n", "NU TE SUPARA, FRATE :D! Next time you will win!";
    }


    public function colorize (array $field) : array
    {
        foreach ($field as $iRow => $row) { // index row
            foreach ($row as $iCol => $col) { // index col
                foreach ($col as $iEntry => $entry) { // index of figure or 'FF' / '..' tile markers
                    if ($entry != ".." || $entry != "FF" || $entry != "  ") {
                        if ($entry[0] == "R") { //if the string starts with
                            $field[$iRow][$iCol][$iEntry] = "\033[31m$entry\033[0m";
                        } elseif ($entry[0] == "G") {
                            $field[$iRow][$iCol][$iEntry] = "\033[92m$entry\033[0m";
                        } elseif ($entry[0] == "B") {
                            $field[$iRow][$iCol][$iEntry] = "\033[34m$entry\033[0m";
                        } elseif ($entry[0] == "Y") {
                            $field[$iRow][$iCol][$iEntry] = "\033[33m$entry\033[0m";
                        }
                    }
                }
            }
        }
        return $field;
    }
}