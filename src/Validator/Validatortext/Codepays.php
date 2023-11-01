<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Codepays extends Constraint
{
  public $message = "Code %string% invalide";

  public function validatedBy()
  {
    return 'code_pays';
  }
}
