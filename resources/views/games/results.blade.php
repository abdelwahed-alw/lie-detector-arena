@extends('layouts.app')

@section('content')
<div class="grid md:grid-cols-3 gap-6">
    <!-- Main content - AI results -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">AI Verdict</h2>
            
            @foreach($player->statements as $i => $statement)
                <div class="mb-4 p-4 border rounded {{ $statement->is_lie ? 'border-red-500 bg-red-50' : 'border-green-500 bg-green-50' }}">
                    <div class="flex justify-between items-start">
                        <span class="font-bold text-lg">{{ $i + 1 }}.</span>
                        <span class="text-sm px-2 py-1 rounded {{ $statement->is_lie ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800' }}">
                            {{ $statement->is_lie ? '🚫 LIE' : '✅ TRUTH' }}
                        </span>
                    </div>
                    <p class="text-gray-800 my-2">{{ $statement->content }}</p>
                    
                    @if($statement->ai_verdict)
                        <div class="mt-2 text-sm">
                            <div class="bg-gray-100 p-2 rounded">
                                Suspicion: <strong>{{ $statement->ai_verdict['score'] }}%</strong>
                                @if($statement->ai_verdict['ai_guess'])
                                    <span class="ml-2 text-red-600 font-bold">← AI thinks this is LIE</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
            
            @if($player->statements->first()?->ai_verdict)
                <div class="bg-purple-100 border-l-4 border-purple-500 p-4 mt-4">
                    <p class="font-bold mb-2"> AI Reasoning:</p>
                    <p class="text-gray-700">{{ $player->statements->first()->ai_verdict['reasoning'] }}</p>
                </div>
            @endif
        </div>
        
        <!-- Voting form (if game still in playing status) -->
        @if($game->status === 'playing')
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="font-bold mb-4"> Cast Your Vote</h3>
            <p class="text-sm text-gray-600 mb-4">Which statement do you think is the lie?</p>
            
            <form action="/votes" method="POST">
                @csrf
                <input type="hidden" name="voter_id" value="{{ session('player_id') }}">
                
                @foreach($player->statements as $statement)
                <div class="mb-3">
                    <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="statement_id" value="{{ $statement->id }}" required class="ml-3">
                        <span>{{ $statement->content }}</span>
                    </label>
                </div>
                @endforeach
                
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Submit Vote
                </button>
            </form>
        </div>
        @endif
    </div>
    
    <!-- Leaderboard sidebar -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
            <h3 class="font-bold mb-4"> Leaderboard</h3>
            
            @if(isset($leaderboard) && $leaderboard->count() > 0)
                <div class="space-y-3">
                    @foreach($leaderboard as $index => $p)
                    <div class="flex justify-between items-center p-2 {{ $index == 0 ? 'bg-yellow-100 rounded' : '' }}">
                        <div class="flex items-center">
                            <span class="w-6 text-gray-500">{{ $index + 1 }}.</span>
                            <span class="font-medium">{{ $p->nickname }}</span>
                            @if($index == 0)  @endif
                        </div>
                        <span class="font-bold text-blue-600">{{ $p->score }} pts</span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No players yet</p>
            @endif
            
            <!-- Auto-refresh meta tag for live updates -->
            @if($game->status === 'playing')
                <meta http-equiv="refresh" content="5">
            @endif
        </div>
    </div>
</div>
@endsection