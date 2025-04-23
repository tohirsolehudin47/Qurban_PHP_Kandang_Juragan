</main>

    <footer class="bg-white border-t mt-auto">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Qurban App</h3>
                    <p class="text-gray-600 mb-4">Platform Qurban dan Tabungan Terpercaya untuk kemudahan ibadah Qurban Anda.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-green-600">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-green-600">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-green-600">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="/about.php" class="text-gray-600 hover:text-green-600">
                                <i class="fas fa-info-circle mr-2"></i>Tentang Kami
                            </a>
                        </li>
                        <li>
                            <a href="/catalog.php" class="text-gray-600 hover:text-green-600">
                                <i class="fas fa-book mr-2"></i>Katalog Produk
                            </a>
                        </li>
                        <li>
                            <a href="/savings.php" class="text-gray-600 hover:text-green-600">
                                <i class="fas fa-piggy-bank mr-2"></i>Tabungan Qurban
                            </a>
                        </li>
                        <li>
                            <a href="/faq.php" class="text-gray-600 hover:text-green-600">
                                <i class="fas fa-question-circle mr-2"></i>FAQ
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Kontak</h3>
                    <ul class="space-y-2">
                        <li class="text-gray-600">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Jl. Contoh No. 123, Jakarta
                        </li>
                        <li>
                            <a href="tel:+6281234567890" class="text-gray-600 hover:text-green-600">
                                <i class="fas fa-phone mr-2"></i>+62 812-3456-7890
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@qurbanapp.com" class="text-gray-600 hover:text-green-600">
                                <i class="fas fa-envelope mr-2"></i>info@qurbanapp.com
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- WhatsApp Support -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Bantuan WhatsApp</h3>
                    <a href="https://wa.me/6281234567890" target="_blank" 
                       class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <i class="fab fa-whatsapp mr-2 text-xl"></i>
                        Chat dengan Kami
                    </a>
                    <p class="text-gray-600 mt-2 text-sm">Layanan chat 24/7</p>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-600 text-sm">
                        &copy; <?php echo date('Y'); ?> Qurban App. All rights reserved.
                    </p>
                    <div class="mt-4 md:mt-0">
                        <a href="/privacy.php" class="text-gray-600 hover:text-green-600 text-sm mx-2">Kebijakan Privasi</a>
                        <a href="/terms.php" class="text-gray-600 hover:text-green-600 text-sm mx-2">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed WhatsApp Button -->
        <a href="https://wa.me/6281234567890" target="_blank" 
           class="fixed bottom-4 right-4 bg-green-500 text-white rounded-full p-3 shadow-lg hover:bg-green-600 transition-colors">
            <i class="fab fa-whatsapp text-2xl"></i>
        </a>
    </footer>
</body>
</html>
