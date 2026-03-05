@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-6 max-w-2xl mx-auto">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold">🗳️ Cast Your Vote</h2>
        <p class="text-gray-600">{{ $player->nickname }}'s statements</p>
    </div>

    <!-- Auto-refresh every 5 seconds while waiting -->
    @if($game->status === 'playing')
        <meta http-equiv="refresh" content="5">
    @endif

    <div class="mb-8">
        @foreach($player->statements as $i => $statement)
            <div class="mb-4 p-4 border rounded bg-gray-50">
                <span class="font-bold">{{ $i + 1 }}.</span>
                <p class="mt-1">{{ $statement->content }}</p>
            </div>
        @endforeach
    </div>

    <form action="/votes" method="POST">
        @csrf
        <input type="hidden" name="voter_id" value="{{ session('player_id') }}">
        
        <div class="mb-6">
            <label class="block mb-3 font-bold">Which statement is the lie?</label>
            @foreach($player->statements as $statement)
                <div class="mb-2">
                    <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="statement_id" value="{{ $statement->id }}" required class="ml-3">
                        <span>{{ $statement->content }}</span>
                    </label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
            Submit Vote
        </button>
    </form>
</div>
@endsection