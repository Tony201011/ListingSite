@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="profileMessageEditor()">
    <div class="max-w-4xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4"><span class="mr-1">&lt;</span> Back to profile settings</a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Profile message</h1>
            <p class="text-gray-600 mb-6">Set a short announcement for promotions, links, or important updates.</p>

            <div class="rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex flex-wrap gap-2 p-3 bg-gray-50 border-b border-gray-200">
                    <button type="button" @click="format('bold')" class="px-3 py-1.5 rounded-md text-sm font-semibold bg-white border border-gray-200 hover:border-pink-300 hover:text-pink-700 transition">B</button>
                    <button type="button" @click="format('italic')" class="px-3 py-1.5 rounded-md text-sm italic bg-white border border-gray-200 hover:border-pink-300 hover:text-pink-700 transition">I</button>
                    <button type="button" @click="format('underline')" class="px-3 py-1.5 rounded-md text-sm underline bg-white border border-gray-200 hover:border-pink-300 hover:text-pink-700 transition">U</button>
                    <button type="button" @click="format('insertUnorderedList')" class="px-3 py-1.5 rounded-md text-sm bg-white border border-gray-200 hover:border-pink-300 hover:text-pink-700 transition">• List</button>
                    <button type="button" @click="format('insertOrderedList')" class="px-3 py-1.5 rounded-md text-sm bg-white border border-gray-200 hover:border-pink-300 hover:text-pink-700 transition">1. List</button>
                    <button type="button" @click="addLink()" class="px-3 py-1.5 rounded-md text-sm bg-white border border-gray-200 hover:border-pink-300 hover:text-pink-700 transition">Link</button>
                </div>

                <div
                    x-ref="editor"
                    contenteditable="true"
                    @input="syncContent()"
                    class="min-h-[180px] px-4 py-3 text-gray-800 focus:outline-none"
                    data-placeholder="Write your profile message..."
                ></div>
            </div>

            <input type="hidden" name="profile_message" x-model="content">

            <div class="mt-4 flex gap-3">
                <button type="button" class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Save message</button>
                <button type="button" @click="clearEditor()" class="px-5 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">Clear</button>
            </div>
        </div>
    </div>
</div>

<style>
    [contenteditable][data-placeholder]:empty:before {
        content: attr(data-placeholder);
        color: #9ca3af;
    }
</style>

<script>
    function profileMessageEditor() {
        return {
            content: '',

            format(command) {
                document.execCommand(command, false, null);
                this.syncContent();
            },

            addLink() {
                const link = prompt('Enter URL');
                if (!link) return;
                document.execCommand('createLink', false, link);
                this.syncContent();
            },

            clearEditor() {
                this.$refs.editor.innerHTML = '';
                this.syncContent();
            },

            syncContent() {
                this.content = this.$refs.editor.innerHTML;
            }
        };
    }
</script>
@endsection
