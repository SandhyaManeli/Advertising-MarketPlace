<?php 

namespace App\Models;

use Moloquent\Eloquent\Model as Moloquent;

class Format extends Moloquent{
  
  protected $connection = 'content';

  public static $FORMAT_TYPE = [
    'ooh' => 0,
    "metro" => 1
  ];

}