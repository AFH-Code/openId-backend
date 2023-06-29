<?php

namespace App\Utils;

class ErrorHttp
{
    public const ERROR = ['message' => 'error', 'code' => 500];
    public const FORM_ERROR = ['message' => 'form invalids', 'code' => 400];
    public const SUB_CAT = ['message' => 'Pas de sous-categorie', 'code' => 400];
    public const CONNEXION_ERROR = ['message' => 'Utilisateur pas connecté', 'code' => 404];
    public const SUCCESS_VOID = ['message' => '', 'code' => 200];
    public const DELETE = ['message' => '', 'code' => 204];
    public const PARAMETER_ID= ['message' => 'Please give the id prameter', 'code' => 201];
    public const INVALID_DATA = ['message' => 'Please give the exist id', 'code' => 201];
    public const DATA_EXIST =  ['message' => 'This category have the subCategories', 'code' => 201];
    public const DATA_NOT_EXIST =  ['message' => 'This data have the subCategories', 'code' => 201];
    public const DATA_LINK_EXIST =  ['message' => 'This category have the links', 'code' => 201];
    public const PARAMETER_NAME= ['message' => 'Please give the name prameter', 'code' => 201];
}