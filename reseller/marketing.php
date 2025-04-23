<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
require_login();
require_reseller();

$reseller_id = $_SESSION['user_id'];

// Fetch marketing materials
$stmt = $pdo->query("
    SELECT * FROM marketing_materials 
    WHERE is_public = 1 
    ORDER BY created_at DESC
");
$materials = $stmt->fetchAll();

// Fetch WhatsApp templates
$stmt = $pdo->query("SELECT * FROM whatsapp_templates ORDER BY name");
$templates = $stmt->fetchAll();

// Fetch affiliate links
$stmt = $pdo->prepare("
    SELECT * FROM affiliate_links 
    WHERE reseller_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$reseller_id]);
$affiliate_links = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Marketing Kit</h1>

    <!-- Marketing Materials -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Materi Marketing</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($materials as $material): ?>
                <div class="border rounded-lg overflow-hidden">
                    <?php if ($material['thumbnail']): ?>
                        <img src="<?php echo htmlspecialchars($material['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($material['title']); ?>"
                             class="w-full h-48 object-cover">
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <h3 class="font-semibold mb-2">
                            <?php echo htmlspecialchars($material['title']); ?>
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            <?php echo htmlspecialchars($material['description']); ?>
                        </p>
                        
                        <div class="flex justify-between items-center">
                            <span class="inline-block px-2 py-1 text-xs rounded-full
                                <?php echo match($material['type']) {
                                    'ebook' => 'bg-blue-100 text-blue-800',
                                    'video' => 'bg-red-100 text-red-800',
                                    'image' => 'bg-green-100 text-green-800',
                                    'document' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800'
                                }; ?>">
                                <?php echo ucfirst($material['type']); ?>
                            </span>
                            
                            <a href="<?php echo htmlspecialchars($material['file_path']); ?>" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- WhatsApp Templates -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Template WhatsApp</h2>
        <div class="space-y-4">
            <?php foreach ($templates as $template): ?>
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold mb-2">
                        <?php echo htmlspecialchars($template['name']); ?>
                    </h3>
                    <pre class="bg-gray-50 p-3 rounded text-sm mb-3 whitespace-pre-wrap">
                        <?php echo htmlspecialchars($template['content']); ?>
                    </pre>
                    <div class="flex justify-end space-x-2">
                        <button onclick="copyTemplate('<?php echo $template['id']; ?>')"
                                class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                        <button onclick="shareTemplate('<?php echo $template['id']; ?>')"
                                class="text-green-600 hover:text-green-800">
                            <i class="fab fa-whatsapp"></i> Share
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Affiliate Links -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Link Affiliate Anda</h2>
            <button onclick="generateNewLink()" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>Buat Link Baru
            </button>
        </div>
        
        <div class="space-y-4">
            <?php foreach ($affiliate_links as $link): ?>
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold">
                                <?php echo ucfirst($link['link_type']); ?> Link
                            </h3>
                            <p class="text-sm text-gray-600">
                                <?php echo htmlspecialchars($link['unique_code']); ?>
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="copyToClipboard('<?php echo htmlspecialchars($link['full_url']); ?>')"
                                    class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-copy"></i>
                            </button>
                            <a href="https://wa.me/?text=<?php echo urlencode($link['full_url']); ?>"
                               target="_blank"
                               class="text-green-600 hover:text-green-800">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                    <input type="text" 
                           value="<?php echo htmlspecialchars($link['full_url']); ?>" 
                           class="mt-2 w-full bg-gray-50 px-3 py-2 rounded text-sm" 
                           readonly>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Teks berhasil disalin!');
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}

function copyTemplate(templateId) {
    // In a real implementation, this would fetch the template with variables filled in
    const template = document.querySelector(`[data-template-id="${templateId}"]`).textContent;
    copyToClipboard(template);
}

function shareTemplate(templateId) {
    // In a real implementation, this would prepare the template for WhatsApp sharing
    const template = document.querySelector(`[data-template-id="${templateId}"]`).textContent;
    window.open(`https://wa.me/?text=${encodeURIComponent(template)}`);
}

function generateNewLink() {
    // In a real implementation, this would open a modal to create a new affiliate link
    alert('Fitur akan segera tersedia!');
}
</script>

<?php include '../includes/footer.php'; ?>
