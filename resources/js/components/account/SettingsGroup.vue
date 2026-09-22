<script setup>
defineProps({
    title: { type: String, required: true },
    items: { type: Array, default: () => [] },
});

const emit = defineEmits(['select']);

function onClick(item) {
    emit('select', item);
}
</script>

<template>
    <section class="settings-group">
        <h2>{{ title }}</h2>
        <div class="settings-list">
            <component
                :is="item.to ? 'router-link' : 'button'"
                v-for="item in items"
                :key="item.title"
                class="setting"
                :to="item.to || undefined"
                :type="item.to ? undefined : 'button'"
                @click="onClick(item)"
            >
                <span class="setting-icon" :style="item.danger ? 'background:#fbe8e7;color:var(--red)' : undefined">
                    <svg><use :href="`#${item.icon}`"></use></svg>
                </span>
                <span>
                    <strong :style="item.danger ? 'color:var(--red)' : undefined">{{ item.title }}</strong>
                    <span>{{ item.subtitle }}</span>
                </span>
                <span v-if="item.toggle" class="toggle" :class="{ off: !item.enabled }" aria-label="Aktif" />
                <svg v-else class="chevron"><use href="#i-chevron"></use></svg>
            </component>
        </div>
    </section>
</template>
