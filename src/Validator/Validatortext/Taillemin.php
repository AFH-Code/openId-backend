<?php
namespace App\Validator\Validatortext;
use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Taillemin extends Constraint
{
  public $message = "la taille de %string% est invalide";
  public $valeur = 4;
}
