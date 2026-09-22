<script setup>
defineProps({
    task: { type: Object, required: true },
});

defineEmits(['toggle']);
</script>

<template>
    <article class="task" :class="{ done: task.done }">
        <div class="task-time" :class="{ late: task.late }">
            <template v-if="task.late">Telat<br>1 hari</template>
            <template v-else>{{ task.hour }}<br>{{ task.minute }}</template>
        </div>
        <div>
            <h3>{{ task.title }}</h3>
            <p>{{ task.subtitle }}</p>
        </div>
        <button class="check-button" type="button" :aria-label="task.done ? 'Tandai belum selesai' : 'Tandai selesai'" @click="$emit('toggle', task)">
            <svg><use href="#i-check"></use></svg>
        </button>
    </article>
</template>
