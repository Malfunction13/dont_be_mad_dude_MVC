<?php


class Helper
{
    function getMiddle(array $field) : int
    {

        return intval(floor(count($field)/2));
    }

    function getLast(array $field) : int
    {

        return count($field)-1;
    }
}