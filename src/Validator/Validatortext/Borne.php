<?php
namespace App\Validator\Validatortext;
use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Borne extends Constraint
{
  public $message = 'la valeur est très petite';
  public $min = 0;
  public $max = 50;
}
