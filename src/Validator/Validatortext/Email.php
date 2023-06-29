<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Email extends Constraint
{
  public $message = "Email %string% invalide";

  public function validatedBy()
  {
    return 'email_user';
  }
}
