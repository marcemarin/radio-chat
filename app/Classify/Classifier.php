<?php

namespace App\Classify;

interface Classifier
{
    /** @param array{program:?string,station:?string,context:?string,contact_name:?string,is_audio:bool,phone_prefix:?string} $ctx */
    public function classify(string $text, array $ctx = []): Classification;
}
