<?php


class PlayerService
{
    function findPlayerKeyByName (Board $board, string $pName) : int
    {
        foreach ($board->players as $key => $player) {
            echo "\n", "LOOKING TO FIND $pName IN KEY $key, VALUE $player->team";
            if ($player->team == $pName) {
                echo "\n", "SUCCESS RETURNING $key";
                return $key;
            }
        }
    }

    function findPlayerByFig(Board $board, string $figName) : int
    {
        // loop through all players and their figures until u find the same figure name as the passed $name
        foreach ($board->players as $key => $player) {
            foreach ($player->figures as $figure){
                if ($figure->name === $figName) {

                    return $key;
                }
            }
        }
    }

    function changeCurrentPlayer (Board $board)
    {
        $previousPlayerKey = $board->currentPlayer;
        //if the current player was last in the list
        if ($previousPlayerKey >= array_key_last($board->players)) { //OR he was deleted (eg player 4 deleted, now biggest key is 3 or 2)
            $board->currentPlayer = array_key_first($board->players); // set currentPlayer to the the first
        } else { // if he wasnt last, the finished player might've been first or in the middle
            $checkedKey = $previousPlayerKey+1; // we dont know if the next in order exists at all
            while (!array_key_exists($checkedKey, $board->players)) // if it was player1's turn and player2 already finished
                $checkedKey ++; //, the loop would try player3 and so on until finds a key that is bigger than the current and exists
            $board->currentPlayer = $checkedKey;
        }
    }


    function removePlayer(Board $board)
    {
        unset($board->players[$board->currentPlayer]);
    }
}