<?php
set_include_path('C:\Users\User\PhpstormProjects\dont_be_mad_dude_MVC');
require 'models/Player.php';
require 'models/Board.php';

class PregameService
{
    public Helper $helper;

    public function __construct(Helper $helper) {
        $this->helper = $helper;
    }

    public function makePlayer($team): Player
    {

        return new Player($team);
    }

    public function makeBoard() : Board {
        $gameField = array_fill(0, 15, array_fill(0, 15, ["  "]));
        $this->drawField($gameField);
        $this->drawFinish($gameField);

        return new Board($gameField);
    }

    public function drawField(array &$field)
    {
        $middle = $this->helper->getMiddle($field);

        foreach ($field as &$row) {
            $row[$middle-1] = ["..", ];
            $row[$middle] = ["..", ];
            $row[$middle+1] = ["..", ];

        }
        unset($row);

        foreach ($field as $index=>&$row)
        {
            if ($index == $middle-1  || $index == $middle || $index == $middle+1) {

                foreach($field[$index] as &$cols) {
                    $cols = ["..", ];
                }
            }
        }
        unset($row);
        unset($cols);
    }

    public function drawFinish(array &$field)
    {
        $middle = $this->helper->getMiddle($field);

        // finish line horizontal
        foreach ($field[$middle] as $index => &$col) {
            if ($index != 0 && $index != count($field)-1) {
                $col = ["FF", ];
            }
        }
        unset($horizontal);

        // finish line vertical
        foreach ($field as $index => &$row) {
            if ($index != 0 && $index != count($field)-1) {
                $row[$middle] = ["FF", ];
            }

        }
    }
}