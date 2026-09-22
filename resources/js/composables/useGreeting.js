import { computed } from 'vue';

function jakartaHour(date = new Date()) {
    const hour = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        hourCycle: 'h23',
        timeZone: 'Asia/Jakarta',
    }).format(date);
    return Number(hour);
}

export function greetingForHour(hour) {
    if (hour >= 4 && hour < 11) {
        return 'Selamat pagi';
    }
    if (hour >= 11 && hour < 15) {
        return 'Selamat siang';
    }
    if (hour >= 15 && hour < 19) {
        return 'Selamat sore';
    }
    return 'Selamat malam';
}

export function useGreeting() {
    const greeting = computed(() => greetingForHour(jakartaHour()));
    const dateLabel = computed(() => new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        timeZone: 'Asia/Jakarta',
    }).format(new Date()));

    return { greeting, dateLabel };
}
