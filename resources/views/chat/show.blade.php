<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $chatSession->title }}
            </h2>
            <a href="{{ route('chat.index', $chatSession->knowledgeSet) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                Back to Chat List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Chat Messages -->
                    <div id="chat-messages" class="space-y-4 mb-4 h-[500px] overflow-y-auto">
                        @foreach($chatSession->messages as $message)
                            <!-- User Message -->
                            <div class="flex justify-end mb-4">
                                <div class="bg-blue-500 text-white rounded-lg py-2 px-4 max-w-[80%]">
                                    {{ $message->message }}
                                </div>
                            </div>
                            <!-- AI Response -->
                            <div class="flex justify-start mb-4">
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg py-2 px-4 max-w-[80%]">
                                    {{ $message->response }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Chat Input -->
                    <div class="border-t dark:border-gray-700 pt-4">
                        <form id="chat-form" class="flex gap-4">
                            <input type="text" 
                                   id="message-input"
                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                   placeholder="Type your message...">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                Send
                            </button>
                        </form>
                        <!-- Loading indicator -->
                        <div id="loading-indicator" class="hidden mt-2 text-gray-500 dark:text-gray-400">
                            AI is thinking...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chat-messages');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const loadingIndicator = document.getElementById('loading-indicator');

            // Scroll to bottom of messages
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Add message to chat
            function addMessage(message, isUser = true) {
                const div = document.createElement('div');
                div.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;
                
                // Replace newlines with <br> tags and handle markdown-style lists
                const formattedMessage = message
                    .replace(/\n/g, '<br>')
                    .replace(/(\d+\.\s)/g, '<br>$1')  // Add line break before numbered lists
                    .replace(/^\s*(\d+\.\s)/gm, '$1'); // Clean up extra spaces before numbers
                
                div.innerHTML = `
                    <div class="inline-flex items-center px-4 py-2 ${
                        isUser 
                            ? 'bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800' 
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-500'
                    } border border-transparent rounded-md font-semibold text-xs tracking-widest whitespace-pre-line">
                        ${formattedMessage}
                    </div>
                `;
                chatMessages.appendChild(div);
                scrollToBottom();
            }

            // Handle form submission
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const message = messageInput.value.trim();
                if (!message) return;

                // Clear input and disable form
                messageInput.value = '';
                messageInput.disabled = true;
                loadingIndicator.classList.remove('hidden');

                // Add user message to chat
                addMessage(message, true);

                try {
                    // Send message to server
                    const response = await fetch('{{ route('chat.message', $chatSession) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message })
                    });

                    const data = await response.json();

                    if (data.error) {
                        addMessage('Error: ' + data.error, false);
                    } else {
                        // Add AI response to chat
                        addMessage(data.response, false);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    addMessage('Error sending message. Please try again.', false);
                } finally {
                    // Re-enable form
                    messageInput.disabled = false;
                    loadingIndicator.classList.add('hidden');
                    messageInput.focus();
                }
            });

            // Initial scroll to bottom
            scrollToBottom();

            // Focus input on page load
            messageInput.focus();
        });
    </script>
</x-app-layout>