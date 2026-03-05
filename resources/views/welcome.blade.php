@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
    <h2 class="text-3xl font-bold mb-6 text-center">Welcome to Lie Detector Game</h2>
    
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Create new game -->
        <div class="bg-blue-50 p-6 rounded-lg">
            <h3 class="text-xl font-bold mb-4 text-blue-800"> Create New Game</h3>
            <form action="/games" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Game Name</label>
                    <input type="text" name="name" required 
                           class="w-full border rounded px-3 py-2" 
                           placeholder="e.g., Dinner Game">
                </div>
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Create Game 
                </button>
            </form>
        </div>
        
        <!-- Join existing game -->
        <div class="bg-green-50 p-6 rounded-lg">
            <h3 class="text-xl font-bold mb-4 text-green-800">Join Game</h3>
            <form action="/games/join" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Game Code</label>
                    <input type="text" name="code" required 
                           class="w-full border rounded px-3 py-2" 
                           placeholder="Enter game code">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Your Name</label>
                    <input type="text" name="nickname" required 
                           class="w-full border rounded px-3 py-2" 
                           placeholder="e.g., Ahmed">
                </div>
                <button type="submit" 
                        class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                    Join Game 
                </button>
            </form>
        </div>
    </div>
</div>
@endsection