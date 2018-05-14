<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 27/02/2018
 * Time: 16:03
 * @param $var
 * @return int
 */


class Hello
{

    /**
     * @param $x1
     * @return float|int
     */
    function test2($x1)
    {
        /** @var TYPE_NAME $x */
        $x = $a;
        $this->test2($x);

        return $this->test1($x1);
    }

    /**
     * @param $var
     * @return float|int
     */
    function test1($var)
    {
        return $var * 2;
    }

}