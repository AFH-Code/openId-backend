<?php

namespace App\Service\Naming;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\Transliterator;
use App\Service\Servicetext\GeneralServicetext;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\ConfigurableInterface;

/**
 * CustomNamer.
 *
 * @final
 */
class CustomNamer implements NamerInterface, ConfigurableInterface
{
    /**
     * @var bool
     */
    private $transliterate = false;

    private $generalservicetext;

    /**
     * @var Transliterator
     */
    private $transliterator;

    public function __construct(Transliterator $transliterator, GeneralServicetext $generalservicetext)
    {
        $this->transliterator = $transliterator;
        $this->generalservicetext = $generalservicetext;
    }

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - transliterate: whether the filename should be transliterated or not
     */
    public function configure(array $options): void
    {
        $this->transliterate = isset($options['transliterate']) ? (bool) $options['transliterate'] : $this->transliterate;
    }

    public function name($object, PropertyMapping $mapping): string
    {
        /* @var $file UploadedFile|ReplacingFile */
        $file = $mapping->getFile($object);
        $name = $file->getClientOriginalName();

        if ($this->transliterate) {
            $name = $this->transliterator->transliterate($name);
        }
        $name = $this->generalservicetext->normaliseText($name);

        return \uniqid().'_'.$name;
    }
}