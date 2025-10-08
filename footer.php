<?php
include __DIR__ . '/backend/site_config.php';
?>
<footer class="w-full text-[<?= htmlspecialchars($config['footer_text']) ?>] py-6" style="background-color: <?= htmlspecialchars($config['footer_bg_colour']) ?>;">
  <div class="max-w-6xl mx-auto px-4 text-center">
    <p class="text-sm mt-2">
      Built with ❤️ by Aiden for the QSS Drama Class.
    </p>
    <p class="text-sm">
      © 2025 Drama Manager. All rights reserved.
    </p>
  </div>
</footer>
<?php if ($config['enable_mascot']) {include __DIR__ . '/mascot.php';} ?>
</body>
</html>