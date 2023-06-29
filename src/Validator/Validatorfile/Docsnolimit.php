<?php
namespace App\Validator\Validatorfile;

use Symfony\Component\Validator\Constraint;
/**
* @Annotation
*/
class Docsnolimit extends Constraint
{
	public $message = "le fichier %string% est invalide";
	//l'option taillemax est la taille maximale du fichier  en octect.
	public $taillemax = 1000000000;

	public function validatedBy()
	{
		return 'docs_no_limit';
	}
}
