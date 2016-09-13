<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 09/09/2016
 * Time: 19:21
 */

// so minimal. much nothing.
namespace App\MMScript\Values;

class NullValue extends Value
{
    function __construct()
    {
        parent::__construct(null);
    }
}