<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
class TaillemaxValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint)
  {
    $value = trim($value); //retire les caractères vide en début et en fin du text.
    if($constraint->valeur < strlen($value)) {
    // C'est cette ligne qui déclenche l'erreur pour le formulaire, avec en argument le message
    $this->context->addViolation($constraint->message, array('%string%' => $value));
    }
  }
}
