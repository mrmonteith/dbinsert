<?php

// File: dbinsert.class.php - calls the dbinsert.trait.php
// Created: 4-15-2024
// By: Michael Monteith

require_once 'dbinsert.trait.php'; // Include the trait file

class DBInsertClass
{
    use DBInsertTrait; // Use the trait in your class
}