<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: String,
    label: String,
    placeholder: String,
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-invert max-w-none focus:outline-none min-h-[150px] p-4 text-gray-300',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(() => props.modelValue, (value) => {
    const isSame = editor.value.getHTML() === value;
    if (!isSame) {
        editor.value.commands.setContent(value, false);
    }
});

onBeforeUnmount(() => {
    editor.value.destroy();
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <label v-if="label" class="text-xs font-bold text-cyan-400 uppercase tracking-widest pl-1">{{ label }}</label>
        
        <div class="border border-white/10 rounded-xl bg-white/5 backdrop-blur-sm overflow-hidden focus-within:border-cyan-500/50 focus-within:ring-1 focus-within:ring-cyan-500/50 transition-all">
            <!-- Toolbar -->
            <div v-if="editor" class="flex flex-wrap gap-1 p-2 border-b border-white/10 bg-white/5">
                <button 
                    @click.prevent="editor.chain().focus().toggleBold().run()"
                    :class="{ 'bg-cyan-500/20 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)]': editor.isActive('bold'), 'text-gray-400 hover:bg-white/10': !editor.isActive('bold') }"
                    class="p-1.5 rounded-lg transition-all"
                    title="Bold"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h8a4 4 0 100-8H6v8zm0 0h10a4 4 0 110 8H6v-8z"></path></svg>
                </button>
                <button 
                    @click.prevent="editor.chain().focus().toggleItalic().run()"
                    :class="{ 'bg-cyan-500/20 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)]': editor.isActive('italic'), 'text-gray-400 hover:bg-white/10': !editor.isActive('italic') }"
                    class="p-1.5 rounded-lg transition-all"
                    title="Italic"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </button>
                <div class="w-px h-6 bg-white/10 mx-1 self-center"></div>
                <button 
                    @click.prevent="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    :class="{ 'bg-cyan-500/20 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)]': editor.isActive('heading', { level: 1 }), 'text-gray-400 hover:bg-white/10': !editor.isActive('heading', { level: 1 }) }"
                    class="p-1.5 rounded-lg transition-all font-mono text-xs font-bold"
                >
                    H1
                </button>
                <button 
                    @click.prevent="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="{ 'bg-cyan-500/20 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)]': editor.isActive('heading', { level: 2 }), 'text-gray-400 hover:bg-white/10': !editor.isActive('heading', { level: 2 }) }"
                    class="p-1.5 rounded-lg transition-all font-mono text-xs font-bold"
                >
                    H2
                </button>
                <div class="w-px h-6 bg-white/10 mx-1 self-center"></div>
                <button 
                    @click.prevent="editor.chain().focus().toggleBulletList().run()"
                    :class="{ 'bg-cyan-500/20 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)]': editor.isActive('bulletList'), 'text-gray-400 hover:bg-white/10': !editor.isActive('bulletList') }"
                    class="p-1.5 rounded-lg transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <button 
                    @click.prevent="editor.chain().focus().toggleOrderedList().run()"
                    :class="{ 'bg-cyan-500/20 text-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.2)]': editor.isActive('orderedList'), 'text-gray-400 hover:bg-white/10': !editor.isActive('orderedList') }"
                    class="p-1.5 rounded-lg transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M5 8h.01M5 12h.01M5 16h.01"></path></svg>
                </button>
                <div class="w-px h-6 bg-white/10 mx-1 self-center"></div>
                <button 
                    @click.prevent="editor.chain().focus().undo().run()"
                    class="p-1.5 rounded-lg text-gray-400 hover:bg-white/10 transition-all font-mono text-xs font-bold"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </button>
                <button 
                    @click.prevent="editor.chain().focus().redo().run()"
                    class="p-1.5 rounded-lg text-gray-400 hover:bg-white/10 transition-all font-mono text-xs font-bold"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2m18-12l-6 6m6-6l-6-6"></path></svg>
                </button>
            </div>
            
            <EditorContent :editor="editor" />
        </div>
        <p v-if="placeholder && !modelValue" class="text-[10px] text-gray-500 italic pl-1 mt-1">{{ placeholder }}</p>
    </div>
</template>

<style>
/* Tiptap focus style */
.ProseMirror:focus {
    outline: none;
}
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #6b7280;
  pointer-events: none;
  height: 0;
}
</style>
