<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Highlight;
use App\Models\Message;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalaController extends Controller
{
    public function program(): JsonResponse
    {
        $p = Program::default();

        return response()->json(['id' => $p->id, 'name' => $p->name, 'station' => $p->station, 'context' => $p->context]);
    }

    /** Feed de la Sala: últimos N mensajes, o los posteriores a `after_id` (para reconectar). */
    public function messages(Request $request, Program $program): JsonResponse
    {
        $q = $program->messages()->with(['contact', 'highlight'])->orderByDesc('id');
        if ($after = $request->integer('after_id')) {
            $q->where('id', '>', $after);
        }
        if ($intent = $request->string('intent')->toString()) {
            $q->where('intent', $intent);
        }
        if ($request->boolean('audio_only')) {
            $q->where('type', 'audio');
        }
        $items = $q->limit(min($request->integer('limit', 100), 500))->get();

        return response()->json(['messages' => $items->map->toSala()->values()]);
    }

    public function topics(Program $program): JsonResponse
    {
        $topics = $program->topics()->where('last_at', '>=', now()->subHours(6))
            ->orderByDesc('messages_count')->limit(30)->get();

        return response()->json(['topics' => $topics]);
    }

    public function highlights(Program $program): JsonResponse
    {
        $items = $program->highlights()->with(['message.contact', 'message.highlight'])
            ->whereIn('status', ['pending', 'on_air'])->orderBy('position')->orderBy('id')->get();

        return response()->json(['highlights' => $items->map(fn ($h) => [
            'id' => $h->id, 'status' => $h->status, 'position' => $h->position, 'note' => $h->note,
            'on_air_since' => $h->status === 'on_air' ? $h->updated_at?->toIso8601String() : null,
            'message' => $h->message->toSala(),
        ])->values()]);
    }

    public function highlight(Message $message): JsonResponse
    {
        $h = Highlight::firstOrCreate(
            ['message_id' => $message->id],
            ['program_id' => $message->program_id, 'position' => (int) Highlight::where('program_id', $message->program_id)->max('position') + 1]
        );
        $message->unsetRelation('highlight');
        broadcast(new \App\Events\MessageUpdated($message->fresh()));

        return response()->json(['highlight' => $h]);
    }

    public function updateHighlight(Request $request, Highlight $highlight): JsonResponse
    {
        $data = $request->validate([
            'status' => 'sometimes|in:pending,on_air,done,discarded',
            'position' => 'sometimes|integer|min:0',
            'note' => 'sometimes|nullable|string|max:200',
        ]);
        $highlight->update($data);
        broadcast(new \App\Events\MessageUpdated($highlight->message()->with('contact')->first()));

        return response()->json(['highlight' => $highlight]);
    }

    /** Marca el que está al aire como salido y pone al aire el primero de la cola, en una sola operación. */
    public function next(Program $program): JsonResponse
    {
        \DB::transaction(function () use ($program) {
            $program->highlights()->where('status', 'on_air')->update(['status' => 'done']);
            $nextUp = $program->highlights()->where('status', 'pending')->orderBy('position')->orderBy('id')->first();
            $nextUp?->update(['status' => 'on_air']);
        });
        $touched = $program->highlights()->whereIn('status', ['on_air', 'done'])->latest('updated_at')->limit(2)->with('message.contact')->get();
        foreach ($touched as $h) {
            broadcast(new \App\Events\MessageUpdated($h->message));
        }

        return $this->highlights($program);
    }

    public function media(Message $message): StreamedResponse
    {
        abort_unless($message->media_path && \Storage::disk('local')->exists($message->media_path), 404);

        return \Storage::disk('local')->response($message->media_path, null, [
            'Content-Type' => $message->media_mime ?? 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function feedback(Request $request, Message $message): JsonResponse
    {
        $data = $request->validate(['bad_transcript' => 'required|boolean']);
        $c = $message->classification ?? [];
        $c['bad_transcript'] = $data['bad_transcript'];
        $message->update(['classification' => $c]);

        return response()->json(['ok' => true]);
    }
}
