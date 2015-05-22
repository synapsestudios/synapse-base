<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Image as ParentConstraint;

class Image extends ParentConstraint
{
    public $mimeTypesMessage       = 'NOT_VALID_IMAGE';
    public $sizeNotDetectedMessage = 'IMAGE_SIZE_NOT_DETECTED';
    public $maxWidthMessage        = 'IMAGE_EXCEEDS_MAX_WIDTH';
    public $minWidthMessage        = 'IMAGE_DOES_NOT_MEET_MIN_WIDTH';
    public $maxHeightMessage       = 'IMAGE_EXCEEDS_MAX_HEIGHT';
    public $minHeightMessage       = 'IMAGE_DOES_NOT_MEET_MIN_HEIGHT';
    public $maxRatioMessage        = 'IMAGE_EXCEEDS_MAX_RATIO';
    public $minRatioMessage        = 'IMAGE_DOES_NOT_MEET_MIN_RATIO';
    public $allowSquareMessage     = 'IMAGE_MUST_NOT_BE_SQUARE';
    public $allowLandscapeMessage  = 'IMAGE_MUST_NOT_BE_LANDSCAPE';
    public $allowPortraitMessage   = 'IMAGE_MUST_NOT_BE_PORTRAIT';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\ImageValidator';
    }
}
