<?php

namespace App\Stt;

/** Para desarrollar sin API keys: devuelve un texto plausible según la duración del archivo. */
class FakeTranscriber implements Transcriber
{
    public function name(): string
    {
        return 'fake';
    }

    public function transcribe(string $absPath, string $mime, string $lang = 'es'): Transcript
    {
        usleep(300_000);
        $samples = [
            'Hola buen día, les habla Juan de Garupá, quería contarles que en el barrio estamos sin luz desde anoche.',
            'Buenas, soy Marta de Villa Cabello, un saludo enorme para todo el equipo, los escucho todas las mañanas.',
            'Che, ¿pueden pasar el tema nuevo de Los Palmeras? Gracias, abrazo desde Candelaria.',
            'Quería denunciar que la calle Lavalle está intransitable, hace dos semanas que nadie viene a arreglar.',
            '¿Sigue el sorteo de las entradas? ¿Cómo participo? Soy Lucía de Posadas.',
            'Buen día, soy Ramón de Apóstoles. El colectivo de las siete no pasó otra vez, ya es la tercera vez esta semana.',
            'Hola, les escribe Silvia desde Encarnación. ¿Saben si el puente está con demoras hoy?',
            'Acá en Itaembé Guazú hace tres días que no pasa el camión de la basura, por favor que alguien avise.',
            'Un saludo para mi viejo Héctor que cumple setenta años hoy, los escuchamos desde San Ignacio.',
            'Totalmente de acuerdo con lo que dijeron recién del boleto, no puede ser que aumente otra vez.',
            'Soy Nico de Oberá, ¿pueden pasar algo de cumbia para arrancar el viernes? Gracias genios.',
            'Quería avisar que en la avenida Uruguay hay un semáforo que no anda y ya casi chocan dos autos.',
        ];

        return new Transcript(
            text: $samples[crc32(basename($absPath)) % count($samples)],
            provider: $this->name(),
            ms: 300,
        );
    }
}
