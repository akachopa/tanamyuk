import { reactive } from 'vue';

export const demoPlans = [
    {
        id: 'gratis',
        code: 'free',
        name: 'Gratis',
        price: 0,
        price_label: 'Rp0',
        period: 'Selamanya',
        description: 'Mulai menanam tanpa biaya.',
        features: ['1 lahan', '3 siklus aktif', 'Agenda dasar'],
    },
    {
        id: 'plus-bulanan',
        code: 'plus_monthly',
        name: 'Plus Bulanan',
        price: 29000,
        price_label: 'Rp29.000',
        period: 'per bulan',
        description: 'Pengingat dan catatan yang lebih lengkap.',
        features: ['Lahan tanpa batas', 'Semua siklus', 'Catatan biaya dan panen'],
        featured: false,
    },
    {
        id: 'plus-tahunan',
        code: 'plus_yearly',
        name: 'Plus Tahunan',
        price: 249000,
        price_label: 'Rp249.000',
        period: 'per tahun',
        description: 'Hemat untuk yang menanam sepanjang tahun.',
        features: ['Semua fitur Plus', 'Prioritas bantuan', 'Setara Rp20.750/bulan'],
        featured: true,
    },
];

export const demoCommodities = [
    { id: 'pakcoy', name: 'Pakcoy' },
    { id: 'cabai', name: 'Cabai Rawit' },
    { id: 'tomat', name: 'Tomat' },
    { id: 'kangkung', name: 'Kangkung' },
    { id: 'bayam', name: 'Bayam' },
    { id: 'selada', name: 'Selada' },
];

export const demoFaqs = [
    {
        question: 'Apakah TanamYuk bisa digunakan tanpa internet?',
        answer: 'Bisa. Agenda, jurnal, biaya, panen, dan panduan yang sudah diunduh tetap dapat dibuka. Perubahan akan disinkronkan ketika koneksi tersedia.',
        keywords: 'offline internet sinkronisasi',
    },
    {
        question: 'Kapan waktu terbaik menyiram tanaman?',
        answer: 'Umumnya pagi sebelum matahari terik atau sore saat suhu menurun. Ikuti juga kebutuhan komoditas dan kondisi media tanam.',
        keywords: 'siram penyiraman air tanaman',
    },
    {
        question: 'Bagaimana mengetahui jadwal pemupukan?',
        answer: 'Jadwal dibuat otomatis saat siklus tanam diaktifkan. Buka menu Agenda untuk melihat waktu dan instruksi pemupukan.',
        keywords: 'pupuk nutrisi jadwal',
    },
    {
        question: 'Bagaimana membayar paket Plus?',
        answer: 'Pilih paket, kemudian pilih QRIS atau Virtual Account. Paket aktif otomatis setelah pembayaran terverifikasi.',
        keywords: 'qris virtual account va bayar pembayaran',
    },
    {
        question: 'Bagaimana mencatat hasil panen?',
        answer: 'Tekan tombol tambah, pilih Hasil Panen, kemudian isi komoditas, jumlah, satuan, dan pemanfaatannya.',
        keywords: 'panen catat hasil',
    },
];

export const demo = reactive({
    hero: {
        garden: 'Kebun Samping Rumah',
        headline: 'Rawat sedikit, panen lebih dekat.',
        weather: '29°',
        weatherLabel: 'Cerah berawan',
        weekDone: 4,
        weekTotal: 6,
        meta: '3 tanaman aktif • Panen terdekat 8 hari lagi',
    },
    stats: [
        { icon: 'i-leaf', value: '3', label: 'Tanaman aktif' },
        { icon: 'i-calendar', value: '3', label: 'Tugas hari ini' },
        { icon: 'i-basket', value: '4,8', label: 'Kg dipanen' },
    ],
    tasks: [
        { id: 'demo-check', title: 'Cek kondisi tanaman', subtitle: 'Semua tanaman • Selesai 07.12', hour: '07', minute: '00', late: false, done: true, scope: 'agenda', minutes: 5 },
        { id: 'demo-water', title: 'Siram cabai rawit', subtitle: '15 tanaman • ±10 menit', hour: '17', minute: '00', late: false, done: false, scope: 'both', minutes: 10 },
        { id: 'demo-nutrition', title: 'Cek nutrisi pakcoy', subtitle: '40 lubang • ±5 menit', hour: '17', minute: '15', late: false, done: false, scope: 'both', minutes: 5 },
        { id: 'demo-trim', title: 'Pangkas daun tomat', subtitle: '8 tanaman • ±15 menit', hour: '', minute: '', late: true, done: false, scope: 'both', minutes: 15 },
    ],
    plants: [
        { name: 'Pakcoy', day: 'Hari ke-22', badge: 'Tumbuh baik', stage: 'Pembesaran', harvest: '8 hari panen', progress: 72 },
        { name: 'Cabai Rawit', day: 'Hari ke-48', badge: 'Mulai berbunga', stage: 'Pembungaan', harvest: '32 hari panen', progress: 58 },
        { name: 'Tomat', day: 'Hari ke-35', badge: 'Perlu dipangkas', stage: 'Vegetatif', harvest: '40 hari panen', progress: 46 },
    ],
    tip: {
        title: 'Tips hari ini',
        body: 'Siram langsung ke media, hindari membasahi daun saat sore.',
    },
    gardens: [
        {
            id: 'demo-garden',
            name: 'Kebun Samping Rumah',
            location: 'Tanah Patah, Bengkulu • Aktif',
            area: '12 m²',
            sun: '6 jam',
            cycles: '3',
            crops: ['Pakcoy', 'cabai', 'tomat'],
            area_m2: 12,
            sunlight_hours: 6,
            water_source: 'keran',
            location_type: 'pekarangan',
            notes: 'Lahan samping rumah, cahaya pagi sampai siang.',
        },
    ],
    cycles: [
        { id: 'demo-pakcoy', title: 'Pakcoy Hidroponik', subtitle: '40 lubang • Hari ke-22 • 72%', icon: 'i-leaf', tone: '', garden_id: 'demo-garden' },
        { id: 'demo-cabai', title: 'Cabai Rawit', subtitle: '15 polybag • Hari ke-48 • 58%', icon: 'i-sun', tone: 'yellow', garden_id: 'demo-garden' },
        { id: 'demo-tomat', title: 'Tomat', subtitle: '8 polybag • Hari ke-35 • 46%', icon: 'i-leaf', tone: 'orange', garden_id: 'demo-garden' },
    ],
    summary: {
        expense: 'Rp86.500',
        harvest: '4,8 kg',
    },
    records: [
        { id: 'rec-1', type: 'journal', title: 'Foto perkembangan pakcoy', subtitle: 'Hari ini, 07.13 • Kebun Samping Rumah', amount: '2 foto', icon: 'i-camera', tone: '' },
        { id: 'rec-2', type: 'harvest', title: 'Panen kangkung', subtitle: '20 September • Dikonsumsi keluarga', amount: '1,8 kg', icon: 'i-basket', tone: 'yellow' },
        { id: 'rec-3', type: 'expense', title: 'Beli nutrisi AB Mix', subtitle: '18 September • Nutrisi dan pupuk', amount: 'Rp42.000', icon: 'i-coin', tone: 'orange' },
        { id: 'rec-4', type: 'issue', title: 'Daun tomat mulai menguning', subtitle: '17 September • Sedang dipantau', amount: '', icon: 'i-warning', tone: 'orange' },
    ],
});

export function homeTasks() {
    return demo.tasks.filter((task) => task.scope === 'both' || task.scope === 'home');
}

export function agendaTasks() {
    return demo.tasks.filter((task) => task.scope === 'both' || task.scope === 'agenda');
}
