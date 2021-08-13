<?php


namespace Workflow\Config;


class Param
{
    public string $location;
    public string $name;
    public string $path = '';
    public string $type; //int, bool, string,  datetime, email, array, map

}