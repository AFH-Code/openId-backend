<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BorneValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint)
  {
    if($value < $constraint->min || $value > $constraint->max){
      if ($value < $constraint->min)
      {
        $constraint->message = 'la valeur doit être supérieure à '.$constraint->min.'.';
      }
      if ($value > $constraint->max)
      {
        $constraint->message = 'la valeur doit être inférieure à '.$constraint->max.'.';
      }
      // C'est cette ligne qui déclenche l'erreur pour le formulaire, avec en argument le message
      $this->context->addViolation($constraint->message);
    }
  }
}
