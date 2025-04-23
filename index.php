<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch some statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'reseller' AND is_active = 1");
$reseller_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_available = 1");
$product_count = $stmt->fetchColumn();

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-green-600 to-green-800 text-white">
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Platform Qurban Modern untuk Ibadah yang Berkah
                </h1>
                <p class="text-xl mb-8">
                    Mudahkan ibadah qurban Anda dengan platform digital terpercaya. Pilih hewan qurban berkualitas atau mulai menabung dari sekarang.
                </p>
                <div class="space-x-4">
                    <a href="/catalog.php" class="bg-white text-green-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        Lihat Katalog
                    </a>
                    <a href="/savings.php" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-green-700 transition-colors">
                        Mulai Menabung
                    </a>
                </div>
            </div>
            <div class="hidden md:block">
                <img src="https://app.nusaqu.id/assets/img/hero-image.png" alt="Qurban App Hero" class="w-full rounded-lg shadow-xl">
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="bg-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold text-green-600 mb-2"><?php echo number_format($reseller_count); ?>+</div>
                <div class="text-gray-600">Reseller Aktif</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-green-600 mb-2"><?php echo number_format($product_count); ?>+</div>
                <div class="text-gray-600">Hewan Qurban</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-green-600 mb-2">24/7</div>
                <div class="text-gray-600">Layanan Pelanggan</div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Keunggulan Platform Kami</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-green-600 text-3xl mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Hewan Berkualitas</h3>
                <p class="text-gray-600">
                    Semua hewan qurban kami dipilih dengan teliti dan memenuhi syarat syar'i.
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-green-600 text-3xl mb-4">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Tabungan Qurban</h3>
                <p class="text-gray-600">
                    Mulai menabung dari sekarang untuk qurban tahun depan dengan skema yang fleksibel.
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-green-600 text-3xl mb-4">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Program Reseller</h3>
                <p class="text-gray-600">
                    Bergabung sebagai reseller dan dapatkan penghasilan tambahan yang menjanjikan.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Product Categories -->
<div class="bg-white py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Kategori Hewan Qurban</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="relative group">
                <img src="https://app.nusaqu.id/assets/img/sapi.jpg" alt="Sapi Qurban" class="w-full h-64 object-cover rounded-lg">
                <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-50 transition-all rounded-lg flex items-center justify-center">
                    <div class="text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">Sapi</h3>
                        <a href="/catalog.php?category=1" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative group">
                <img src="https://app.nusaqu.id/assets/img/kambing.jpg" alt="Kambing Qurban" class="w-full h-64 object-cover rounded-lg">
                <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-50 transition-all rounded-lg flex items-center justify-center">
                    <div class="text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">Kambing</h3>
                        <a href="/catalog.php?category=2" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative group">
                <img src="https://app.nusaqu.id/assets/img/domba.jpg" alt="Domba Qurban" class="w-full h-64 object-cover rounded-lg">
                <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-50 transition-all rounded-lg flex items-center justify-center">
                    <div class="text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">Domba</h3>
                        <a href="/catalog.php?category=3" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reseller CTA -->
<div class="bg-green-600 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Bergabung Sebagai Reseller</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Dapatkan penghasilan tambahan dengan menjadi reseller kami. Nikmati berbagai keuntungan dan dukungan pemasaran yang lengkap.
        </p>
        <a href="/auth/register.php" class="inline-block bg-white text-green-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
            Daftar Sekarang
        </a>
    </div>
</div>

<!-- Testimonials -->
<div class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Testimoni Reseller</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <img src="https://app.nusaqu.id/assets/img/testimonial1.jpg" alt="Testimonial 1" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-semibold">Ahmad Syafiq</h4>
                        <p class="text-gray-600">Reseller Jakarta</p>
                    </div>
                </div>
                <p class="text-gray-600">
                    "Alhamdulillah, dengan bergabung di Qurban App, saya bisa membantu banyak orang menunaikan ibadah qurban sekaligus mendapatkan penghasilan tambahan."
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <img src="https://app.nusaqu.id/assets/img/testimonial2.jpg" alt="Testimonial 2" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-semibold">Siti Aminah</h4>
                        <p class="text-gray-600">Reseller Bandung</p>
                    </div>
                </div>
                <p class="text-gray-600">
                    "Platform yang sangat membantu dengan sistem yang professional. Dukungan tim sangat responsif dan helpful."
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <img src="https://app.nusaqu.id/assets/img/testimonial3.jpg" alt="Testimonial 3" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-semibold">Muhammad Rizki</h4>
                        <p class="text-gray-600">Reseller Surabaya</p>
                    </div>
                </div>
                <p class="text-gray-600">
                    "Komisi yang diberikan sangat menarik dan sistem pembayarannya tepat waktu. Sangat recommended!"
                </p>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp CTA -->
<div class="bg-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Butuh Bantuan?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto text-gray-600">
            Tim kami siap membantu Anda 24/7. Hubungi kami melalui WhatsApp untuk konsultasi gratis.
        </p>
        <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
            <i class="fab fa-whatsapp text-2xl mr-2"></i>
            Chat dengan Kami
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
