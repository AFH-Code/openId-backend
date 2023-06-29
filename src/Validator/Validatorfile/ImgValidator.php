<?php
//(c) Noel Kenfack Novembre 2014 (propriété de zentsoft entreprise)
namespace App\Validator\Validatorfile;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Service\Servicetext\GeneralServicetext;

class ImgValidator extends ConstraintValidator
{
private $service;
public function __construct(GeneralServicetext $service)
{
$this->service = $service;
}

public function validate($file, Constraint $constraint)
{
 $extension = array('gif', 'GIF','jpg','JPG', 'png','PNG', 'bmp','BMP', 'jpeg','JPEG');
 if($file === null)
 {
	$text = '';
	$extensionfile = 'gif';
	$size = 0;
 }else{
	$text = $file->getClientOriginalName();
	$extensionfile = $file->getClientOriginalExtension();
	$size = $file->getSize();
 }
 $text = $this->service->normaliseText($text);
if($constraint->taillemax < $size || !in_array($extensionfile, $extension))
 {
	 if (!in_array($extensionfile, $extension))
	 {
	 $constraint->message = "L'extension de votre fichier n'est pas pris en compte";
	 }else{
	 $constraint->message = "La taille de votre image est insupportable";
	 }

	 // C'est cette ligne qui déclenche l'erreur pour le formulaire, avec en argument le message
	 $this->context->addViolation($constraint->message, array('%string%' => $file->getClientOriginalName()));
 }
}
}
