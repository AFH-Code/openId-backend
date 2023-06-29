<?php
namespace App\Validator\Validatorfile;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class Image extends Constraint
{
  public $message = "l'image %string% est invalide";
  //l'option taillemax est la taille maximale de l'image en octect.
  public $taillemax = 20000;

  public function validatedBy()
  {
  return 'my_image';
  }
}
