<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { store, show } from '@/actions/App/Http/Controllers/MessageController';
import { ref } from 'vue';

const page = usePage();

// --- Create note ---
const createdCode = ref('');
const codeCopied = ref(false);

const createForm = useForm({ content: '' });

function saveNote() {
    createForm.post(store.url(), {
        preserveState: true,
        onSuccess: () => {
            createdCode.value = page.props.flash?.code ?? '';
            createForm.reset();
        },
    });
}

function copyCode() {
    navigator.clipboard.writeText(createdCode.value).then(() => {
        codeCopied.value = true;
        setTimeout(() => (codeCopied.value = false), 2000);
    });
}

// --- Read note ---
const revealedContent = ref('');

const revealForm = useForm({ code: '' });

function getNote() {
    revealForm.post(show.url(), {
        preserveState: true,
        onSuccess: () => {
            revealedContent.value = page.props.flash?.content ?? '';
            revealForm.reset();
        },
    });
}
</script>

<template>
    <Head title="Secure Message" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-950 px-4 py-12 text-white">
        <div class="w-full max-w-md space-y-8">

            <!-- Header -->
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight">Secure Message</h1>
                <p class="mt-1 text-sm text-gray-500">Encrypted. Self-destructs after reading.</p>
            </div>

            <!-- Create Note -->
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="mb-4 font-semibold text-gray-200">Create a Note</h2>

                <template v-if="!createdCode">
                    <textarea
                        v-model="createForm.content"
                        rows="5"
                        placeholder="Type your secret message…"
                        class="w-full resize-none rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-gray-600 outline-none transition focus:border-white/25"
                        :class="{ 'border-red-500/50': createForm.errors.content }"
                    />
                    <p v-if="createForm.errors.content" class="mt-2 text-sm text-red-400">{{ createForm.errors.content }}</p>
                    <button
                        @click="saveNote"
                        :disabled="createForm.processing || !createForm.content.trim()"
                        class="mt-3 w-full rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-950 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        {{ createForm.processing ? 'Encrypting…' : 'Save Note' }}
                    </button>
                </template>

                <template v-else>
                    <p class="mb-3 text-sm text-gray-400">Share this message key with your recipient:</p>
                    <div class="flex items-center gap-2 rounded-xl border border-white/10 bg-black/30 px-4 py-3">
                        <span class="flex-1 font-mono text-xl font-bold tracking-[0.15em] text-white">{{ createdCode }}</span>
                        <button
                            @click="copyCode"
                            class="shrink-0 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-medium transition hover:bg-white/20"
                        >
                            {{ codeCopied ? 'Copied!' : 'Copy' }}
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-yellow-500/70">Valid for 24 hours or until read once.</p>
                    <button
                        @click="createdCode = ''"
                        class="mt-4 text-xs text-gray-600 underline-offset-4 hover:text-gray-400 hover:underline"
                    >
                        Create another note
                    </button>
                </template>
            </div>

            <!-- Divider -->
            <div class="flex items-center gap-3">
                <div class="h-px flex-1 bg-white/10" />
                <span class="text-xs text-gray-600">or</span>
                <div class="h-px flex-1 bg-white/10" />
            </div>

            <!-- Read Note -->
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="mb-4 font-semibold text-gray-200">Read a Note</h2>

                <template v-if="!revealedContent">
                    <input
                        v-model="revealForm.code"
                        type="text"
                        placeholder="Enter message key…"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 font-mono text-sm uppercase tracking-widest text-white placeholder-gray-600 outline-none transition focus:border-white/25"
                        :class="{ 'border-red-500/50': revealForm.errors.code }"
                        @keyup.enter="getNote"
                    />
                    <p v-if="revealForm.errors.code" class="mt-2 text-sm text-red-400">{{ revealForm.errors.code }}</p>
                    <button
                        @click="getNote"
                        :disabled="revealForm.processing || !revealForm.code.trim()"
                        class="mt-3 w-full rounded-xl border border-white/15 bg-transparent px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        {{ revealForm.processing ? 'Retrieving…' : 'Get Note' }}
                    </button>
                </template>

                <template v-else>
                    <p class="mb-2 text-xs font-medium uppercase tracking-widest text-gray-500">Message</p>
                    <div class="mb-4 rounded-xl border border-white/20 bg-white/10 px-4 py-4">
                        <p class="whitespace-pre-wrap break-words text-base leading-relaxed text-white">{{ revealedContent }}</p>
                    </div>
                    <p class="text-xs text-red-400">This message has been permanently deleted from our servers.</p>
                    <button
                        @click="revealedContent = ''"
                        class="mt-4 text-xs text-gray-600 underline-offset-4 hover:text-gray-400 hover:underline"
                    >
                        Read another note
                    </button>
                </template>
            </div>

        </div>
    </div>
</template>
