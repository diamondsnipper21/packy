<template>
    <quill-editor 
        theme="snow" 
        :toolbar="toolbarOptions" 
        :content="value" 
        contentType="html"
        :placeholder="placeholder" 
        :modules="modules" 
        @update:content="updateContent"
    ></quill-editor>
</template>

<script>
    import { QuillEditor } from '@vueup/vue-quill'
    import BlotFormatter from 'quill-blot-formatter'
    import { ImageDrop } from 'quill-image-drop-module';
    import '@vueup/vue-quill/dist/vue-quill.snow.css';

    export default {
        name: 'WysiwygEditor',
        components: {
            QuillEditor
        },
        props: ['value', 'placeholder', 'toolbarOptions', 'mutator', 'selfobj'],

        setup(props) {
            const toolbarOptions = [['bold', 'italic', 'underline'], ['link', 'image']];

            const modules = [
                {
                    name: 'imageDrop',
                    module: ImageDrop
                },
                {
                    name: 'blotFormatter',
                    module: BlotFormatter
                }
            ]

            function updateContent(content) {
                props.selfobj.$store.commit(props.mutator, content);
            }

            return {
                toolbarOptions,
                modules,
                updateContent,
            }
        },
    }
</script>
