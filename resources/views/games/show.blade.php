@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">🎮 {{ $game->name }}</h2>
        <div class="bg-gray-200 px-4 py-2 rounded">
            Game Code: <span class="font-mono font-bold">{{ $game->id }}</span>
        </div>
    </div>
    
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Players list -->
        <div class="md:col-span-1 bg-gray-50 p-4 rounded-lg">
            <h3 class="font-bold mb-4">Players ({{ $game->players->count() }})</h3>
            <ul class="space-y-2">
                @foreach($game->players as $player)
                    <li class="bg-white p-2 rounded shadow flex justify-between">
                        <span>{{ $player->nickname }}</span>
                        <span class="text-sm text-gray-500">{{ $player->score }} points</span>
                    </li>
                @endforeach
            </ul>
        </div>
        
        <!-- Statement submission form -->
        <div class="md:col-span-2">
            <h3 class="font-bold mb-4">Write 3 statements about yourself</h3>
            <p class="text-sm text-gray-600 mb-4">2 truths + 1 lie</p>
            <p style="color:red">DEBUG - Player ID: {{ session('player_id') }}</p>
            <form action="/statements" method="POST">
                @csrf
                <input type="hidden" name="player_id" value="{{ session('player_id') }}">
<!-- DEBUG: player_id = {{ session('player_id') }} -->
                
                @for($i = 0; $i < 3; $i++)
                <div class="mb-4 p-4 border rounded {{ $i == 2 ? 'bg-red-50' : 'bg-green-50' }}">
                    <label class="block mb-2 font-medium">
                        {{ $i + 1 }}. 
                        {{ $i == 2 ? '🚫 Lie statement' : '✅ Truth statement' }}
                    </label>
                    <input type="text" 
                           name="statements[{{ $i }}][content]" 
                           required 
                           maxlength="200"
                           class="w-full border rounded px-3 py-2"
                           placeholder="Write your statement here...">
                </div>
                @endfor
                
                <div class="mb-4">
                    <label class="block mb-2 font-bold">Which statement is the lie?</label>
                    <select name="lie_index" required class="w-full border rounded px-3 py-2">
                        <option value="0">Statement 1 is the lie</option>
                        <option value="1">Statement 2 is the lie</option>
                        <option value="2">Statement 3 is the lie</option>
                    </select>
                </div>
                
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 w-full">
                    Submit to AI 
                </button>
            </form>
        </div>
    </div>
</div>
@endsection