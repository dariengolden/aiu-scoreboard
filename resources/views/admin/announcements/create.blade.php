@extends('layouts.admin')

@section('title', 'Create Announcement')

@section('content')
<div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('admin.announcements.index') }}" class="hover:text-white transition-colors">Announcements</a>
    <span>/</span>
    <span class="text-white font-medium">Create</span>
</div>

<form action="{{ route('admin.announcements.store') }}" method="POST" id="announcement-form">
    @csrf

    <div class="bg-[#1e293b] rounded-2xl border border-white/5 p-6">
        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-slate-400 mb-2">
                Title
            </label>
            <input type="text"
                   name="title"
                   id="title"
                   value="{{ old('title') }}"
                   class="w-full px-4 py-2.5 rounded-xl bg-[#0f172a] border border-white/10 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                   placeholder="Enter announcement title..."
                   required>
        </div>

        <div class="mb-4">
            <label for="content" class="block text-sm font-medium text-slate-400 mb-2">
                Content
            </label>

            <div class="flex items-center gap-1 mb-2 p-2 bg-[#0f172a] rounded-t-xl border border-b-0 border-white/10">
                <button type="button" onclick="formatText('bold')" class="p-2 rounded hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Bold">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4h5a4 4 0 014 4 4 4 0 01-4 4H4V4zm0 8h6a4 4 0 014 4 4 4 0 01-4 4H4v-8z" clip-rule="evenodd"/></svg>
                </button>
                <button type="button" onclick="formatText('italic')" class="p-2 rounded hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Italic">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4h4l-2 12H6l2-12z" clip-rule="evenodd"/></svg>
                </button>
                <button type="button" onclick="formatText('underline')" class="p-2 rounded hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Underline">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3h2v8a3 3 0 106 0V3h2v8a5 5 0 01-10 0V3zM4 17h12v2H4v-2z"/></svg>
                </button>
                <span class="w-px h-6 bg-white/10 mx-1"></span>
                <button type="button" onclick="formatText('insertUnorderedList')" class="p-2 rounded hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Bullet List">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4h2v2H4V4zm4 0h8v2H8V4zM4 8h2v2H4V8zm4 0h8v2H8V8zm-4 4h2v2H4v-2zm4 0h8v2H8v-2z" clip-rule="evenodd"/></svg>
                </button>
                <button type="button" onclick="formatText('insertOrderedList')" class="p-2 rounded hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Numbered List">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4h2v2H3V4zm4 0h10v2H7V4zm0 4h10v2H7V8zm0 4h10v2H7v-2zm-4 4h2v2H3v-2zm0-8h2v2H3v-2z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            <div id="editor"
                 class="w-full px-4 py-3 rounded-b-xl bg-[#0f172a] border border-white/10 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[200px] prose prose-invert max-w-none"
                 contenteditable="true"
                 data-placeholder="Enter announcement content...">{{ old('content') }}</div>

            <input type="hidden" name="content" id="content-input">
        </div>

        <div class="mb-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox"
                       name="is_active"
                       id="is_active"
                       value="1"
                       {{ old('is_active') ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-white/20 bg-[#0f172a] text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                <span class="text-sm font-medium text-slate-300">Show announcement modal</span>
            </label>
            <p class="mt-1 text-xs text-slate-500 ml-8">When enabled, the modal will appear on every page load.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-medium rounded-lg transition-colors">
                Create Announcement
            </button>
            <a href="{{ route('admin.announcements.index') }}"
               class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-medium rounded-lg transition-colors">
                Cancel
            </a>
        </div>
    </div>
</form>

<script>
    function formatText(command) {
        document.execCommand(command, false, null);
        document.getElementById('editor').focus();
    }

    document.getElementById('announcement-form').addEventListener('submit', function(e) {
        const editor = document.getElementById('editor');
        const input = document.getElementById('content-input');
        input.value = editor.innerHTML;
    });
</script>

<style>
    #editor:empty:before {
        content: attr(data-placeholder);
        color: #64748b;
    }
    #editor ul, #editor ol {
        margin-left: 1.5rem;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    #editor li {
        margin-bottom: 0.25rem;
    }
</style>
@endsection
