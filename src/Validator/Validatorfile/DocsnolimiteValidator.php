<?php
namespace App\Validator\Validatorfile;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DocsnolimiteValidator extends ConstraintValidator
{

public function validate($file, Constraint $constraint)
{
	$extension = array('rar','RAR','zip','ZIP','DOCX','docx','PDF','pdf');
	if ($file === null)
	{
	 $size = 0;
	 $extensionfile = 'rar';
	}else{
	 $extensionfile = $file->getClientOriginalExtension();
	 $size = $file->getClientSize();
	}

	if($constraint->taillemax < $size || !in_array($extensionfile, $extension))
	{
		if(!in_array($extensionfile, $extension))
		{
			$constraint->message = "l'extension de votre fichier n'est pas pris en compte";
		}
		if($constraint->taillemax < $size)
		{
			$constraint->message = "la taille du fichier est très grande.";
		}

		// C'est cette ligne qui déclenche l'erreur pour le formulaire, avec en argument le message
		$this->context->addViolation($constraint->message, array('%string%' => $file->getClientOriginalName()));
	}
}

}
