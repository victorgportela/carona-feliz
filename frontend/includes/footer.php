    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= asset('js/main.js') ?>"></script>
    
    <?php if (isset($additionalJs)): ?>
        <?= $additionalJs ?>
    <?php endif; ?>
</body>
</html> 