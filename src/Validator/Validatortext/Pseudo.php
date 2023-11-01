<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Pseudo extends Constraint
{
  public $message = "Votre pseudo %string% est invalide";

  public function validatedBy()
  {
    return 'pseudo_user';
  }
}
