<script setup>
defineProps({
    expense: { type: String, default: 'Rp0' },
    harvest: { type: String, default: '0 kg' },
});
</script>

<template>
    <div class="summary-card">
        <div class="summary-item">
            <span>Total biaya bulan ini</span>
            <strong>{{ expense }}</strong>
        </div>
        <div class="summary-item">
            <span>Hasil panen</span>
            <strong>{{ harvest }}</strong>
        </div>
    </div>
</template>
