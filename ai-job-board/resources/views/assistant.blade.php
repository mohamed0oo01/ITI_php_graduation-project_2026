@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content')
@php
    $suggestions = Auth::user()->role === 'candidate'
        ? ['What are the best jobs for me?', 'Which jobs match my skills?', 'What skills should I learn?']
        : ['How many candidates are registered?', 'Which job has the most applications?', 'List all available jobs.', 'Show jobs in the Programming category.'];
@endphp

<div class="chat-container max-w-5xl mx-auto h-[82vh] min-h-[32rem] bg-white rounded-2xl shadow border border-khaki/40 overflow-hidden flex flex-col md:flex-row">

    {{-- Sidebar --}}
    <aside id="sidebar" class="bg-jet text-almond w-full md:w-64 shrink-0 flex-col md:flex hidden">
        <div class="sidebar-header flex items-center justify-between px-4 py-3 border-b border-black/30">
            <span class="font-bold text-almond tracking-tight">AI Job Board</span>
        </div>

        <div class="p-3">
            <button type="button" id="new-chat-btn"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-khaki text-black rounded-lg text-sm font-medium hover:bg-almond transition-colors">
                <span class="text-base leading-none">+</span> New conversation
            </button>
        </div>

        <div class="history-list flex-1 overflow-y-auto px-3 pb-4 space-y-2">
            <p class="text-xs uppercase tracking-wider text-almond/50 px-2 pt-1">History</p>
            <div id="history-items" class="space-y-2">
                <p class="text-sm text-almond/50 px-2 pt-1 text-center" id="history-empty">No conversations yet.</p>
            </div>
        </div>
    </aside>

    {{-- Main chat area --}}
    <main class="flex-1 flex flex-col min-w-0 min-h-0 bg-almond/40">

        <div class="chat-header flex items-center gap-3 px-4 py-3 bg-almond border-b border-khaki/40">
            <button type="button" id="sidebar-toggle" class="md:hidden px-2 py-1 rounded-md text-brown hover:bg-khaki/40" title="Toggle sidebar">&#9776;</button>
            <div>
                <p class="font-semibold text-jet leading-tight">AI Assistant</p>
                <p class="text-xs text-brown">Powered by Google Gemini</p>
            </div>
            <button type="button" id="delete-current-btn" class="ml-auto text-xs px-2.5 py-1 rounded-md text-red-600 border border-red-300 hover:bg-red-50 hidden">Delete conversation</button>
        </div>

        <div id="messages" class="chat-body flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
            <div class="flex justify-start">
                <div class="max-w-[85%]">
                    <div class="px-4 py-3 rounded-2xl rounded-tl-sm bg-white text-jet border border-khaki/40 text-sm leading-relaxed whitespace-pre-wrap">
                        Hi {{ Auth::user()->name }}! I'm your AI assistant.
                        {{ Auth::user()->role === 'candidate'
                            ? 'Ask me what the best jobs are for you, which jobs match your skills, or what skills you should learn.'
                            : 'Ask me about candidates, the most applied job, or all available jobs.' }}
                    </div>
                    <div class="mt-1.5 ml-1 flex gap-1">
                        <button class="text-xs w-7 h-7 rounded-md bg-khaki/30 text-brown hover:bg-khaki/60 flex items-center justify-center" title="Regenerate">&crarr;</button>
                        <button class="text-xs w-7 h-7 rounded-md bg-khaki/30 text-brown hover:bg-khaki/60 flex items-center justify-center" title="Copy">Copy</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="chat-input-area bg-white border-t border-khaki/40 p-3 sm:p-4">
            @if (! empty($suggestions))
                <div class="flex flex-wrap gap-2 mb-2.5">
                    @foreach ($suggestions as $suggestion)
                        <button type="button" class="suggestion-chip text-xs px-3 py-1.5 rounded-full bg-almond text-brown border border-khaki/50 font-medium hover:bg-khaki hover:text-black transition-colors">
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            @endif

            <form id="chat-form" class="flex gap-2 items-end">
                <textarea id="chat-input" rows="1" placeholder="What would you like to know?"
                          class="flex-1 resize-none px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brown text-sm"></textarea>
                <button type="submit" class="px-4 py-2.5 rounded-lg bg-brown text-white text-sm font-medium hover:bg-black transition-colors whitespace-nowrap">
                    Send
                </button>
            </form>
        </div>
    </main>
</div>

<div class="max-w-5xl mx-auto mt-3 text-xs text-brown/70">
    Messages are handled server-side using the Gemini API with a key from your environment. No key is exposed to the browser.
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const messages = document.getElementById('messages');
    const historyItems = document.getElementById('history-items');
    const historyEmpty = document.getElementById('history-empty');
    const deleteCurrentBtn = document.getElementById('delete-current-btn');

    const state = {
        conversationId: null,
        lastUserMessage: null,
    };

    function api(path, options = {}) {
        return fetch(path, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, ...(options.body ? { 'Content-Type': 'application/json' } : {}) },
            ...options,
        }).then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok && !data.success) throw data;
            return { status: res.status, data };
        });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        if (isNaN(d)) return '';
        return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function renderHistory(conversations) {
        historyItems.innerHTML = '';

        if (!conversations.length) {
            historyItems.appendChild(historyEmpty);
            historyEmpty.classList.remove('hidden');
            return;
        }
        historyEmpty.classList.add('hidden');

        conversations.forEach((conversation) => {
            const item = document.createElement('div');
            item.className = 'history-item cursor-pointer rounded-lg px-3 py-2.5 hover:bg-black/30 border border-transparent ' +
                (state.conversationId === conversation.id ? 'bg-black/25 border-black/20' : '');

            const title = document.createElement('p');
            title.className = 'text-sm leading-snug truncate';
            title.textContent = conversation.title;

            const meta = document.createElement('div');
            meta.className = 'flex items-center justify-between mt-1';

            const date = document.createElement('span');
            date.className = 'text-xs text-almond/50';
            date.textContent = formatDate(conversation.updated_at);

            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'text-xs text-almond/60 hover:text-red-400 px-1';
            del.title = 'Delete conversation';
            del.textContent = 'x';
            del.addEventListener('click', async (e) => {
                e.stopPropagation();
                await api('/api/ai/chat/' + conversation.id, { method: 'DELETE' });
                if (state.conversationId === conversation.id) openNewChat();
                loadHistory();
            });

            meta.append(date, del);
            item.append(title, meta);

            item.addEventListener('click', () => openConversation(conversation.id));
            historyItems.appendChild(item);
        });
    }

    function loadHistory() {
        api('/api/ai/conversations').then(({ data }) => renderHistory(data.conversations));
    }

    function openConversation(id) {
        state.conversationId = id;
        deleteCurrentBtn.classList.remove('hidden');
        historyItems.querySelectorAll('.history-item').forEach((el) => el.classList.remove('bg-black/25', 'border-black/20'));

        api('/api/ai/conversations/' + id).then(({ data }) => {
            const conversation = data.conversation;
            messages.querySelectorAll('.flex').forEach((n) => n.remove());

            conversation.messages.forEach((message) => {
                if (message.role === 'user') {
                    state.lastUserMessage = message.content;
                    addMessage(message.content, 'user');
                } else {
                    addMessage(message.content, 'ai');
                }
            });
        });
    }

    function openNewChat() {
        state.conversationId = null;
        deleteCurrentBtn.classList.add('hidden');
        messages.querySelectorAll('.flex').forEach((n) => n.remove());
        addMessage('Start a new conversation. What would you like to know?', 'ai');
    }

    function makeActions(bubbleText) {
        const wrap = document.createElement('div');
        wrap.className = 'mt-1.5 ml-1 flex gap-1';

        const copy = document.createElement('button');
        copy.type = 'button';
        copy.className = 'text-xs px-2 h-7 rounded-md bg-khaki/30 text-brown hover:bg-khaki/60 flex items-center';
        copy.textContent = 'Copy';
        copy.addEventListener('click', () => navigator.clipboard?.writeText(bubbleText));

        wrap.appendChild(copy);
        return wrap;
    }

    function addMessage(text, sender) {
        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (sender === 'user' ? 'justify-end' : 'justify-start');

        const inner = document.createElement('div');
        inner.className = 'max-w-[85%] ' + (sender === 'user' ? '' : 'min-w-[40%]');

        const bubble = document.createElement('div');
        bubble.className = 'px-4 py-3 rounded-2xl text-sm leading-relaxed whitespace-pre-wrap ' +
            (sender === 'user'
                ? 'bg-brown text-white rounded-br-sm'
                : 'bg-white text-jet border border-khaki/40 rounded-tl-sm');
        bubble.textContent = text;
        inner.appendChild(bubble);

        if (sender === 'ai') {
            inner.appendChild(makeActions(text));
        }

        wrap.appendChild(inner);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
        return bubble;
    }

    function submitMessage(message) {
        state.lastUserMessage = message;
        addMessage(message, 'user');

        const waiting = addMessage('Thinking...', 'ai');

        const payload = { message };
        if (state.conversationId) payload.conversation_id = state.conversationId;

        api('/api/ai/chat', {
            method: 'POST',
            body: JSON.stringify(payload),
        }).then(({ data }) => {
            state.conversationId = data.conversation_id;
            deleteCurrentBtn.classList.remove('hidden');
            waiting.textContent = data.message;
            loadHistory();
        }).catch((data) => {
            waiting.textContent = 'Sorry, something went wrong: ' + (data.message ?? 'unknown error');
        });
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        input.value = '';
        input.style.height = 'auto';
        submitMessage(message);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 160) + 'px';
    });

    document.querySelectorAll('.suggestion-chip').forEach((chip) => {
        chip.addEventListener('click', () => submitMessage(chip.textContent.trim()));
    });

    document.getElementById('new-chat-btn').addEventListener('click', () => {
        openNewChat();
        historyItems.querySelectorAll('.history-item').forEach((el) => el.classList.remove('bg-black/25', 'border-black/20'));
    });

    document.getElementById('sidebar-toggle').addEventListener('click', () => {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('hidden');
        sidebar.classList.toggle('flex');
    });

    deleteCurrentBtn.addEventListener('click', async () => {
        if (!state.conversationId) return;
        await api('/api/ai/chat/' + state.conversationId, { method: 'DELETE' });
        openNewChat();
        loadHistory();
    });

    loadHistory();
</script>
@endsection