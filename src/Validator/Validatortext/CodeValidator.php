<?php
//(c) Noel Kenfack Novembre 2014 (propriété de zentsoft entreprise)
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Service\Servicetext\GeneralServicetext;

class CodeValidator extends ConstraintValidator
{
  private $service;
  public function __construct(GeneralServicetext $service)
  {
    $this->service = $service;
  }

  public function validate($value, Constraint $constraint)
  {
    if(!$this->service->codepays($value))
    {
      $this->context->addViolation($constraint->message, array('%string%' => $value));
    }
  }
}
