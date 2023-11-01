<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Telephone extends Constraint
{
  public $message = "numéro %string% invalide";

  public function validatedBy()
  {
    return 'adresse_telephone';
  }
}
