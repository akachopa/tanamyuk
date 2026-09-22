const PLANTS = [
    {
        name: 'Kangkung',
        method: 'Polybag',
        sun: [3, 8],
        minutes: 10,
        area: 0.5,
        levels: ['baru', 'pernah', 'rutin'],
        methods: ['polybag', 'tanah'],
        goals: ['konsumsi', 'hemat', 'hobi'],
        reason: 'Panen cepat dan toleran jika jadwalmu padat.',
    },
    {
        name: 'Bayam',
        method: 'Tanah',
        sun: [4, 8],
        minutes: 15,
        area: 1,
        levels: ['baru', 'pernah', 'rutin'],
        methods: ['tanah', 'polybag'],
        goals: ['konsumsi', 'hemat', 'hobi'],
        reason: 'Mudah dirawat dan cocok untuk konsumsi harian.',
    },
    {
        name: 'Pakcoy',
        method: 'Hidroponik',
        sun: [4, 7],
        minutes: 20,
        area: 1,
        levels: ['baru', 'pernah', 'rutin'],
        methods: ['hidroponik', 'polybag'],
        goals: ['konsumsi', 'hemat', 'hobi', 'jualan'],
        reason: 'Siklus pendek, hasil rapi, dan cocok di lahan sempit.',
    },
    {
        name: 'Selada',
        method: 'Hidroponik',
        sun: [4, 6],
        minutes: 20,
        area: 1,
        levels: ['pernah', 'rutin', 'baru'],
        methods: ['hidroponik', 'vertikultur'],
        goals: ['konsumsi', 'hobi', 'jualan'],
        reason: 'Nyaman di tempat teduh sebagian dan panen bertahap.',
    },
    {
        name: 'Cabai Rawit',
        method: 'Polybag',
        sun: [6, 10],
        minutes: 15,
        area: 2,
        levels: ['pernah', 'rutin', 'baru'],
        methods: ['polybag', 'tanah'],
        goals: ['konsumsi', 'hemat', 'jualan', 'hobi'],
        reason: 'Sekali tanam bisa dipetik berkali-kali.',
    },
    {
        name: 'Tomat',
        method: 'Polybag',
        sun: [6, 10],
        minutes: 25,
        area: 3,
        levels: ['pernah', 'rutin'],
        methods: ['polybag', 'tanah'],
        goals: ['konsumsi', 'hobi', 'jualan'],
        reason: 'Hasil melimpah bila cahaya cukup dan rutin dipangkas.',
    },
];

function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
}

export function recommendPlants(answers) {
    const sun = Number(answers.sunlight_hours || 0);
    const minutes = Number(answers.care_minutes || 0);
    const area = Number(answers.area_m2 || 0);
    const level = answers.experience || 'baru';
    const method = answers.method || 'polybag';
    const goal = answers.goal || 'konsumsi';

    return PLANTS.map((plant) => {
        let score = 58;
        const reasons = [];

        if (sun >= plant.sun[0] && sun <= plant.sun[1]) {
            score += 14;
            reasons.push('Jam sinar matahari sesuai.');
        } else if (Math.abs(sun - plant.sun[0]) <= 2) {
            score += 6;
        } else {
            score -= 8;
        }

        if (minutes >= plant.minutes) {
            score += 10;
            reasons.push('Waktu perawatan harian mencukupi.');
        } else {
            score -= 6;
        }

        if (area >= plant.area) {
            score += 8;
        } else {
            score -= 10;
        }

        if (plant.levels.includes(level)) {
            score += 8;
            if (level === 'baru') {
                reasons.push('Ramah untuk pemula.');
            }
        } else {
            score -= 8;
        }

        if (plant.methods.includes(method)) {
            score += 10;
            reasons.push(`Cocok dengan metode ${method}.`);
        }

        if (plant.goals.includes(goal)) {
            score += 6;
        }

        reasons.unshift(plant.reason);

        return {
            name: plant.name,
            method: plant.method,
            score: clamp(score, 42, 98),
            reasons: reasons.slice(0, 3),
        };
    }).sort((a, b) => b.score - a.score).slice(0, 4);
}

export function mapApiRecommendations(payload) {
    const list = payload?.recommendations || payload?.results || payload?.data || payload;
    if (!Array.isArray(list)) {
        return [];
    }
    return list.map((item) => ({
        name: item.commodity?.name || item.name || item.commodity_name || 'Tanaman',
        method: item.method || item.cultivation_method || '',
        score: Number(item.score ?? 0),
        reasons: Array.isArray(item.reasons) ? item.reasons : (item.reason ? [item.reason] : []),
    })).sort((a, b) => b.score - a.score);
}
