<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Chat') }} - {{ $knowledgeSet->name }}
            </h2>
            <form action="{{ route('chat.store', $knowledgeSet) }}" method="POST">
                @csrf
                <x-primary-button>
                    {{ __('New Chat') }}
                </x-primary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($sessions->isEmpty())
                        <p class="text-center">No chat sessions yet. Start a new chat!</p>
                    @else
                        <div class="space-y-4">
                            @foreach($sessions as $session)
                                <a href="{{ route('chat.show', $session) }}" 
                                   class="block p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h3 class="text-lg font-semibold">{{ $session->title }}</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Created {{ $session->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="text-gray-400">
                                            {{ $session->messages->count() }} messages
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>