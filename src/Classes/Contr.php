<?php
namespace App\Classes;

//class control to send order to put in  database 
class Contr extends Connect
{
     public function addpro( $data)
    {
         $this->setOrder($data );
         
        }
    }
