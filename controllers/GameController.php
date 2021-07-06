<?php
set_include_path('C:\Users\User\PhpstormProjects\dont_be_mad_dude_MVC');
require "services/FigureService.php";
require "services/PlayerService.php";
require "services/StatusService.php";
require "services/MovementService.php";


class gameController
{
    public Board $board;
    public View $view;
    public PlayerService $playerService;
    public FigureService $figureService;
    public MovementService $movementService;
    public StatusService $statusService;
    public Helper $helper;

    public bool $gameOver;
    public array $finalists;

    public function __construct (Board $board, View $view,
                                 PlayerService $playerService,
                                 FigureService $figureService,
                                 MovementService $movementService,
                                 StatusService $statusService,
                                 Helper $helper)
    {
        $this->board = $board;
        $this->view = $view;
        $this->playerService = $playerService;
        $this->figureService = $figureService;
        $this->movementService = $movementService;
        $this->statusService = $statusService;
        $this->helper = $helper;

        $this->gameOver = false;
        $this->finalists = [];
    }

    public function handleTurn() : bool
    {
        $this->view->turnAnnouncement($this->board);
        $moves = $this->view->throwDice($this->board->players[$this->board->currentPlayer]);
//        $moves = 6;

        if (count($this->board->players[$this->board->currentPlayer]->figures) === 0) { // if the player has no figures on the field
            if ($moves === 6) { //if the player throws six
                $this->addFigure(); //automatically a figure will be added

                return false; // no switch of players will occur - 6 grants extra throw
            } else {
                $this->view->needSix();

                return true; // the player loses his turn cuz needs a 6 to get out on the field
            }
        } else { //otherwise the player has at least 1 figure
            if ($moves === 6) { // 6 grants a bonus move and allows to spawn a figure
                if ($this->statusService->allowedToSpawn($this->board->players[$this->board->currentPlayer])) {  // if the player is eligible to spawn more figures
                    $choice = $this->view->moveOrSpawn();
                    if ($choice === "MOVE") {  // the player should get the choice to move his active figures or spawn
                        if($this->handleMove($moves)) { // if it was a move where a figure finished
                            if ($this->statusService->isFinishedPlayer($this->board->players[$this->board->currentPlayer])) { // we check if the player finished with all 4 figs
                                $this->handleFinishedPlayer(); // we remove him from the list

                                return true; // and we change control to the next player
                            }
                        }
                    } else { // the player chose to spawn
                        $this->addFigure();
                    }
                } else { // if the player is not allowed to spawn anymore - i.e has 1 active but 3 finished figures
                    if($this->handleMove($moves)) { // if it was a move where a figure finished
                        if($this->statusService->isFinishedPlayer($this->board->players[$this->board->currentPlayer])) { // we check if the player finished with all 4 figs
                            $this->handleFinishedPlayer();

                            return true; // and we change control to the next player
                        }
                    }
                }

                return false; //if the finishing condition wasnt met the player gets his bonus roll
            } else { // if it is not a 6 proceed as usual
                $this->handleMove($moves); // if the player throws 4 and finishes the controll will anyway be passed to next player
                if($this->statusService->isFinishedPlayer($this->board->players[$this->board->currentPlayer])) {
                    $this->handleFinishedPlayer();

                }
                return true;
            }
        }

    }

    public function addFigure()
    {
        $starterCoords = [
            "R" => [$this->helper->getMiddle($this->board->field) - 1, 0],
            "G" => [0, $this->helper->getMiddle($this->board->field) + 1],
            "B" => [$this->helper->getMiddle($this->board->field) + 1, $this->helper->getLast($this->board->field)],
            "Y" => [$this->helper->getLast($this->board->field), $this->helper->getMiddle($this->board->field) - 1],
        ];

        // create new figure object with proper parameters for different teams
        $newFig = $this->figureService->createFigure(
            $this->board->players[$this->board->currentPlayer],
            $starterCoords[$this->board->players[$this->board->currentPlayer]->team]);

        // add it to the list of figures for the current player
        array_push($this->board->players[$this->board->currentPlayer]->figures, $newFig);

        // set the current active figure to the new figure
        $this->board->players[$this->board->currentPlayer]->currentFigure = $newFig;

        // handle collisions and write new fig to the board
        // finally assign the slot to the current player
        $this->movementService->newPosUpdate($this->board->field, $newFig, $this->handleCollision());
    }


    public function handleCollision () : bool|string
    { //at this stage only yx of the current figure is updated
        $y = $this->board->players[$this->board->currentPlayer]->currentFigure->y;
        $x = $this->board->players[$this->board->currentPlayer]->currentFigure->x;
        $destination = $this->board->field[$y][$x];
        if ($destination[0] == ".." || $destination[0] == "FF") { //just a free tile

            return false; // false for no collision
        }
        else { // consider non-free tile scenarios
            $enemies = $this->figureService->findEnemies($destination, $this->board->players[$this->board->currentPlayer]->team); //make a list of enemies

            if (!$enemies) { //non-empty array evaluates to true, otherwise findEnemies will return false
                //if there were N figures there but all were friendly or fortified

                return false; // we wont have collision
            }
            else { // find enemies returned array of count at least 1
                if (count($enemies) === 1) { //if it is just one enemy, no point of triggering choices
                    $this->figureService->removeFigure($this->board, $enemies[0]); //remove it from the enemy players list of figs

                    return $enemies[0]; // return the name of the fig for fieldUpdate()
                }
                else { // otherwise there is more than 1 attackable enemy
                    $choice = $this->view->getFigureChoice($enemies);
                    $this->figureService->removeFigure($this->board, $choice);

                    return $choice;
                }

            }

        }
    }


    public function handleMove(int $moves) : bool
    {
        if (count($this->board->players[$this->board->currentPlayer]->figures) > 1) {
            //choose which of all figures on the board to move and set the current player accordingly
            $playerFigures = $this->figureService->getFigureNames($this->board->players[$this->board->currentPlayer]->figures);
            $choice = $this->view->getFigureChoice($playerFigures);
            $key = $this->figureService->findFigureKey($this->board, $choice);
            $this->board->players[$this->board->currentPlayer]->currentFigure = $this->board->players[$this->board->currentPlayer]->figures[$key];
        } else { // then the player has 1 figure and the first in the list is taken
            $this->board->players[$this->board->currentPlayer]->currentFigure = $this->board->players[$this->board->currentPlayer]->figures[0];
        }

        $oldY = $this->board->players[$this->board->currentPlayer]->currentFigure->y; // old coords will be required when updating the field
        $oldX = $this->board->players[$this->board->currentPlayer]->currentFigure->x;

        for ($i = 0; $i < $moves; $i++) {  //change YX 1 at a time and update the compass at each step
            $this->movementService->move($this->board, $this->board->players[$this->board->currentPlayer]->currentFigure);
            $this->figureService->updateCompass($this->board, $this->board->players[$this->board->currentPlayer]->currentFigure);
            $finished = $this->isFinished();
            if ($finished) { // at each step check if this is not the finishing point
                $this->updateField($oldY, $oldX, $collision="F");
                $this->figureService->removeFigure($this->board, $this->board->players[$this->board->currentPlayer]->currentFigure->name); // remove the fig from the list
                $this->board->players[$this->board->currentPlayer]->currentFigure = null;

                return true;
            }
        }
        $this->updateField($oldY, $oldX, $this->handleCollision()); // this line will be executed if there was no collision

        return false;
    }


    public function updateField(int $oldX, int $oldY, bool|string $collision)
    {
        $this->movementService->newPosUpdate($this->board->field, $this->board->players[$this->board->currentPlayer]->currentFigure, $collision);
        $this->movementService->oldPosUpdate($this->board, $oldX, $oldY);
    }


    public function switchPlayer()
    {
        $this->playerService->changeCurrentPlayer($this->board);
    }


    public function isFinished() : bool
    {
        if ($this->statusService->isFinishedFigure($this->board)) {
            $this->view->figureFinished($this->board->players[$this->board->currentPlayer]->currentFigure->name);
            $this->board->players[$this->board->currentPlayer]->finishedCount ++; // increment finished count
            return true;
        }

        return false;
    }


    public function handleFinishedPlayer()
    {
        array_push($this->finalists, $this->board->players[$this->board->currentPlayer]); // add to finalists
        $this->playerService->removePlayer($this->board, $this->board->players[$this->board->currentPlayer]->team); // remove from list of players
    }


    public function isGameOver() : bool
    {
        if (count($this->board->players) === 1) {  // if there is just 1 player left, then he is the last in ranklist
            array_push($this->finalists, $this->board->players[$this->board->currentPlayer]);

            return true; // and there is no point to continue the game with 1 player
        }

        return false;
    }


    public function finalResult()
    {
        $this->view->printResult($this->finalists);
    }
}
