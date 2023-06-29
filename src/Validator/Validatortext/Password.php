<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Password extends Constraint
{
  public $message = "Mot de passe %string% invalide";

  public function validatedBy()
  {
    return 'password_user';
  }
}
