<?php

namespace Tests\Feature;

use App\Classify\Classification;
use App\Classify\Classifier;
use App\Classify\JevClassifier;
use Marcemarin\TypeSafe\Contracts\Client;
use Marcemarin\TypeSafe\DecisionRequest;
use Marcemarin\TypeSafe\Facades\TypeSafe;
use Tests\TestCase;

class JevClassifierTest extends TestCase
{
    /** Extractor que registra si lo llamaron, para verificar cuándo se gasta en el LLM. */
    private function extractor(): Classifier
    {
        return new class implements Classifier
        {
            public int $calls = 0;

            public function classify(string $text, array $ctx = []): Classification
            {
                $this->calls++;

                return new Classification('otro', 'neutral', 'corte de luz', 'Garupá', 'Juan', 0, [], 'Resumen del extractor');
            }
        };
    }

    public function test_maps_decisions_and_calls_the_extractor_for_a_good_message(): void
    {
        TypeSafe::fake(['intent' => 'reclamo', 'sentiment' => 'negativo', 'on_air' => 3.6, 'insulto' => 0.01, 'spam' => 0.02, 'datos_personales' => 0.1]);
        $extractor = $this->extractor();

        $c = (new JevClassifier(app(Client::class), $extractor, extractorIsPaid: true))
            ->classify('Soy Juan de Garupá, estamos sin luz desde anoche', ['program' => 'Demo', 'is_audio' => true]);

        $this->assertSame('reclamo', $c->intent);
        $this->assertSame('negativo', $c->sentiment);
        $this->assertSame(90, $c->onAirScore);           // 3.6 sobre 4 niveles
        $this->assertSame([], $c->moderation);
        $this->assertSame('corte de luz', $c->topic);
        $this->assertSame('Garupá', $c->location);
        $this->assertSame(1, $extractor->calls);

        TypeSafe::assertAskedCount(1);                    // seis preguntas, una sola llamada
        TypeSafe::assertAsked(fn (DecisionRequest $r) => $r->has('intent') && $r->has('on_air') && $r->has('insulto')
            && $r->state['is_voice_note_transcript'] === true);
    }

    public function test_flags_insults_and_does_not_spend_on_the_llm(): void
    {
        TypeSafe::fake(['intent' => 'opinion', 'sentiment' => 'negativo', 'on_air' => 0.2, 'insulto' => 0.96, 'spam' => 0.02, 'datos_personales' => 0.0]);
        $extractor = $this->extractor();

        $c = (new JevClassifier(app(Client::class), $extractor, extractorIsPaid: true))->classify('Son todos unos pelotudos');

        $this->assertSame(['insulto'], $c->moderation);
        $this->assertNull($c->topic);
        $this->assertSame(0, $extractor->calls);
        $this->assertFalse($c->raw['extracted']);
    }

    public function test_uncertain_moderation_goes_to_review_instead_of_being_decided(): void
    {
        TypeSafe::fake(['intent' => 'opinion', 'sentiment' => 'neutral', 'on_air' => 2.0, 'insulto' => 0.5, 'spam' => 0.1, 'datos_personales' => 0.59]);

        $c = (new JevClassifier(app(Client::class), $this->extractor(), extractorIsPaid: false))->classify('Mensaje ambiguo');

        $this->assertSame([], $c->moderation);
        $this->assertSame(['insulto', 'datos_personales'], $c->raw['review']);
    }

    public function test_spam_intent_always_adds_the_spam_flag(): void
    {
        TypeSafe::fake(['intent' => 'spam', 'sentiment' => 'positivo', 'on_air' => 0.0, 'insulto' => 0.0, 'spam' => 0.3, 'datos_personales' => 0.0]);

        $c = (new JevClassifier(app(Client::class), $this->extractor(), extractorIsPaid: true))->classify('PROMO 50% OFF');

        $this->assertContains('spam', $c->moderation);
    }
}
