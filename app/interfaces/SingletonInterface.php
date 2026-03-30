<?php

namespace interfaces;

interface SingletonInterface
{
    public static function instantiate(): object;

    public static function getInstance(): object;
}