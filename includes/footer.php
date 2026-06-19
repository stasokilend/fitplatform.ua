<?php
// includes/footer.php - Футер для всіх сторінок
?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Конфігурація -->
    <script src="<?php echo $basePath; ?>js/config.js"></script>
    <!-- Авторизація -->
    <script src="<?php echo $basePath; ?>js/auth.js"></script>
    <!-- Додаткові скрипти (підставляються зі сторінки) -->
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>