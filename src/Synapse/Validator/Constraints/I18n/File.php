<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\File as ParentConstraint;

class File extends ParentConstraint
{
    public $notFoundMessage    = 'FILE_NOT_FOUND';
    public $notReadableMessage = 'FILE_NOT_READABLE';
    public $maxSizeMessage     = 'FILE_TOO_BIG';
    public $mimeTypesMessage   = 'TYPE_NOT_ALLOWED';

    public $uploadIniSizeErrorMessage   = 'FILE_TOO_BIG';
    public $uploadFormSizeErrorMessage  = 'FILE_TOO_BIG';
    public $uploadPartialErrorMessage   = 'INCOMPLETE_UPLOAD';
    public $uploadNoFileErrorMessage    = 'NO_FILE_UPLOADED';
    public $uploadNoTmpDirErrorMessage  = 'NO_TEMP_FOLDER_CONFIGURED';
    public $uploadCantWriteErrorMessage = 'CANNOT_WRITE_TO_DISK';
    public $uploadExtensionErrorMessage = 'PHP_EXTENSION_ERROR';
    public $uploadErrorMessage          = 'UPLOAD_ERROR';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
