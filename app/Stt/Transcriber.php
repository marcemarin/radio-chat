<?php

namespace App\Stt;

interface Transcriber
{
    public function name(): string;

    public function transcribe(string $absPath, string $mime, string $lang = 'es'): Transcript;
}
