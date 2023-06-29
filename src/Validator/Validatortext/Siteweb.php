<?php
namespace App\Validator\Validatortext;

use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Siteweb extends Constraint
{
  public $message = "Site %string% invalide";

  public function validatedBy()
  {
    return 'siteweb_user';
  }
}
