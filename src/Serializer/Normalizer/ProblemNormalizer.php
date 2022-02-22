<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProblemNormalizer implements NormalizerInterface
{
    public function normalize($exception, string $format = null, array $context = [])
    {
        return [
            'code' => $exception->getStatusCode(),
            'message' => $exception->getMessage()
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof FlattenException;
    }
}
