<?php
/**
 * includes/footer.php
 * Closes what header.php opened and loads the JavaScript.
 */
?>
        </main>

        <footer class="df-footer">
            <?= e(APP_NAME) ?> · A document preparation and workflow assistance system.
            Staff review and confirm all information.
        </footer>
    </div><!-- /.df-main -->
</div><!-- /.df-shell -->

<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<?php foreach (($extraScripts ?? []) as $script): ?>
    <script src="<?= e(url($script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>